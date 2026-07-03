<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CustomerAuthService;
use App\Services\CustomerContext;
use App\Services\OrderNotificationService;
use App\Services\OrderService;
use App\Services\Payments\DTOs\CreatePaymentOrderData;
use App\Services\Payments\DTOs\VerifyPaymentData;
use App\Services\Payments\Exceptions\PaymentException;
use App\Services\Payments\Exceptions\PaymentVerificationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutPaymentService
{
    private const CACHE_PREFIX = 'checkout_payment:';

    private const CACHE_TTL_MINUTES = 30;

    public function __construct(
        private PaymentOrchestrator $paymentOrchestrator,
        private PaymentManager $paymentManager,
        private OrderService $orderService,
        private CustomerContext $customerContext,
        private CustomerAuthService $customerAuthService,
        private OrderNotificationService $orderNotificationService,
        private PaymentSettingsResolver $paymentSettingsResolver,
    ) {}

    public function shouldUsePayBeforeOrder(): bool
    {
        return $this->paymentOrchestrator->supportsOnlineCheckout()
            && ! $this->paymentOrchestrator->shouldSkipGateway();
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public function prepare(Product $product, array $validated): array
    {
        $this->assertPayBeforeOrderEnabled();

        $customer = $this->resolveCustomerForCheckout($validated);
        $quote = $this->orderService->quoteOrder($product, $validated, $customer);

        if ($quote['amount'] <= 0) {
            throw PaymentVerificationException::orderNotPayable();
        }

        $checkoutReference = (string) Str::uuid();
        $currency = (string) ($quote['currency'] ?? 'INR');

        $gateway = $this->paymentManager->driver();
        $result = $gateway->createOrder(new CreatePaymentOrderData(
            orderId: 0,
            orderUuid: $checkoutReference,
            amount: (float) $quote['amount'],
            currency: $currency,
            customerName: (string) ($validated['guest_name'] ?? ''),
            customerEmail: $validated['guest_email'] ?? null,
            customerPhone: (string) ($validated['guest_phone'] ?? ''),
        ));

        Cache::put($this->cacheKey($checkoutReference), [
            'product_id' => $product->id,
            'validated' => $validated,
            'customer_id' => $customer?->id,
            'amount' => (float) $quote['amount'],
            'currency' => $currency,
            'gateway' => $gateway->slug(),
            'gateway_order_id' => $result->gatewayOrderId,
            'created_at' => now()->toIso8601String(),
        ], now()->addMinutes(self::CACHE_TTL_MINUTES));

        $publicConfig = $this->paymentSettingsResolver->frontendConfig();

        return [
            'checkout_reference' => $checkoutReference,
            'key_id' => $publicConfig['key_id'],
            'gateway_order_id' => $result->gatewayOrderId,
            'amount' => $result->amountInSmallestUnit,
            'currency' => $result->currency,
            'display_amount' => $quote['amount'],
            'customer_name' => $validated['guest_name'] ?? '',
            'customer_email' => $validated['guest_email'] ?? null,
            'customer_phone' => $validated['guest_phone'] ?? '',
        ];
    }

    public function finalize(
        string $checkoutReference,
        string $gatewayOrderId,
        string $gatewayPaymentId,
        string $signature,
    ): Order {
        $this->assertPayBeforeOrderEnabled();

        $session = Cache::get($this->cacheKey($checkoutReference));

        if (! is_array($session)) {
            throw new PaymentException(PaymentException::CODE_SESSION_EXPIRED);
        }

        if ($session['gateway_order_id'] !== $gatewayOrderId) {
            throw PaymentVerificationException::orderNotPayable();
        }

        $data = new VerifyPaymentData(
            gatewayOrderId: $gatewayOrderId,
            gatewayPaymentId: $gatewayPaymentId,
            signature: $signature,
            expectedAmount: (float) $session['amount'],
            expectedCurrency: (string) $session['currency'],
        );

        $existingPaid = app(PaymentService::class)->findPaidByGatewayPaymentId(
            (string) $session['gateway'],
            $data->gatewayPaymentId,
        );

        if ($existingPaid !== null && $existingPaid->order !== null) {
            Cache::forget($this->cacheKey($checkoutReference));

            return $existingPaid->order;
        }

        $gateway = $this->paymentManager->driver();
        $result = $gateway->verifyPayment($data);

        if (abs($result->amount - (float) $session['amount']) > 0.01) {
            throw PaymentVerificationException::amountMismatch();
        }

        $product = Product::query()->findOrFail((int) $session['product_id']);
        /** @var array<string, mixed> $validated */
        $validated = $session['validated'];

        $this->ensureCustomerLoggedIn($session);

        $order = DB::transaction(function () use ($product, $validated, $result, $session, $checkoutReference) {
            $order = $this->orderService->createOrderWithVerifiedPayment($product, $validated, $result);
            $order->refresh();

            Cache::forget($this->cacheKey($checkoutReference));

            return $order;
        });

        if ($this->customerContext->isImpersonating()) {
            $impersonator = $this->customerContext->impersonator();
            $customer = $this->customerContext->effectiveCustomer();
            if ($impersonator && $customer) {
                $this->customerContext->logOrderPlaced($impersonator, $customer, $order->id);
            }
        }

        $this->orderNotificationService->notifyOrderPlaced($order);
        $this->orderNotificationService->notifyPaymentVerified($order);

        return $order;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolveCustomerForCheckout(array $validated): ?User
    {
        $customer = $this->customerContext->effectiveCustomer();

        if ($customer !== null) {
            return $customer;
        }

        $email = (string) ($validated['guest_email'] ?? '');
        $this->customerAuthService->assertOtpVerifiedFor($email);

        return $this->customerAuthService->resolveCustomerForVerifiedEmail(
            $email,
            (string) $validated['guest_phone'],
            (string) $validated['guest_name'],
        );
    }

    /**
     * @param  array<string, mixed>  $session
     */
    private function ensureCustomerLoggedIn(array $session): ?User
    {
        $customer = $this->customerContext->effectiveCustomer();

        if ($customer !== null) {
            return $customer;
        }

        if (! isset($session['customer_id'])) {
            return null;
        }

        $customer = User::query()->find((int) $session['customer_id']);

        if ($customer === null) {
            return null;
        }

        $this->customerAuthService->loginCustomer($customer);

        return $customer;
    }

    private function assertPayBeforeOrderEnabled(): void
    {
        if (! $this->shouldUsePayBeforeOrder()) {
            throw new PaymentException(PaymentException::CODE_THEME_NOT_SUPPORTED);
        }
    }

    private function cacheKey(string $checkoutReference): string
    {
        return self::CACHE_PREFIX.$checkoutReference;
    }
}
