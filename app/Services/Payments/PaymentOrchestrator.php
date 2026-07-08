<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Setting;
use App\Services\CustomerContext;
use App\Services\Payments\DTOs\CreatePaymentOrderData;
use App\Services\Payments\DTOs\CreatePaymentOrderResult;
use App\Services\Payments\DTOs\VerifyPaymentData;
use App\Services\Payments\DTOs\VerifyPaymentResult;
use App\Services\Payments\Exceptions\PaymentException;
use App\Services\Payments\Exceptions\PaymentGatewayException;
use App\Services\Payments\Exceptions\PaymentVerificationException;

class PaymentOrchestrator
{
    public function __construct(
        private CustomerContext $customerContext,
        private PaymentManager $paymentManager,
        private PaymentService $paymentService,
        private PaymentSettingsResolver $settingsResolver,
    ) {}

    public function shouldSkipGateway(): bool
    {
        return $this->customerContext->isImpersonating();
    }

    public function supportsOnlineCheckout(): bool
    {
        return active_theme() === 'better-buns'
            && $this->settingsResolver->isOnlineCheckoutEnabled();
    }

    public function initializeOrderPayment(Order $order): void
    {
        if ($this->shouldSkipGateway()) {
            $order->payment_method = Order::PAYMENT_METHOD_CASH_ON_STORE;
            $order->payment_status = Order::PAYMENT_STATUS_PENDING;
            $order->placed_by_user_id = $this->customerContext->impersonator()?->id;

            return;
        }

        if (active_theme() === 'better-buns') {
            $order->payment_method = Order::PAYMENT_METHOD_RAZORPAY;
            $order->payment_status = Order::PAYMENT_STATUS_PENDING;

            return;
        }

        $order->payment_method = Order::PAYMENT_METHOD_UPI;
        $order->payment_status = Order::PAYMENT_STATUS_PENDING;
    }

    /**
     * @return array{result: CreatePaymentOrderResult, payment: \App\Models\Payment}
     */
    public function initiateCheckout(Order $order): array
    {
        $this->assertOnlineCheckoutAllowed($order);

        if ($order->isPaymentVerified()) {
            throw new PaymentException(PaymentException::CODE_ORDER_ALREADY_PAID);
        }

        $currency = (string) (Setting::get('currency') ?: config('payment.currency', 'INR'));

        $data = new CreatePaymentOrderData(
            orderId: $order->id,
            orderUuid: $order->uuid,
            amount: (float) $order->amount,
            currency: $currency,
            customerName: $order->guest_name,
            customerEmail: $order->guest_email,
            customerPhone: $order->guest_phone,
        );

        $gateway = $this->paymentManager->driver();
        $result = $gateway->createOrder($data);
        $payment = $this->paymentService->createPendingPayment($order, $result);

        return [
            'result' => $result,
            'payment' => $payment,
        ];
    }

    public function completeCheckout(Order $order, VerifyPaymentData $data): VerifyPaymentResult
    {
        $this->assertOnlineCheckoutAllowed($order);

        if ($order->isPaymentVerified()) {
            throw new PaymentException(PaymentException::CODE_ORDER_ALREADY_PAID);
        }

        $existingPaid = $this->paymentService->findPaidByGatewayPaymentId(
            $this->paymentManager->defaultDriver(),
            $data->gatewayPaymentId,
        );

        if ($existingPaid !== null && $existingPaid->order_id === $order->id) {
            if (! $order->isPaymentVerified()) {
                $order->payment_status = 'verified';
                $order->save();
            }

            return new VerifyPaymentResult(
                gatewayOrderId: $data->gatewayOrderId,
                gatewayPaymentId: $data->gatewayPaymentId,
                signature: $data->signature,
                amount: (float) $existingPaid->amount,
                currency: (string) $existingPaid->currency,
                gateway: (string) $existingPaid->payment_gateway,
                rawResponse: $existingPaid->response_payload ?? [],
            );
        }

        $gateway = $this->paymentManager->driver();
        $result = $gateway->verifyPayment($data);

        if (abs($result->amount - (float) $order->amount) > 0.01) {
            throw PaymentVerificationException::amountMismatch();
        }

        $payment = $this->paymentService->findPendingForOrder(
            $order,
            $data->gatewayOrderId,
            $gateway->slug(),
        );

        if ($payment === null) {
            throw PaymentVerificationException::orderNotPayable();
        }

        try {
            $this->paymentService->markPaid($payment, $result);
        } catch (\Throwable $e) {
            $this->paymentService->markFailed($payment, $e instanceof PaymentException ? $e->customerCode() : PaymentException::CODE_UNKNOWN);

            throw $e;
        }

        return $result;
    }

    private function assertOnlineCheckoutAllowed(Order $order): void
    {
        if ($this->shouldSkipGateway()) {
            throw PaymentVerificationException::orderNotPayable();
        }

        if (active_theme() !== 'better-buns') {
            throw new PaymentException(PaymentException::CODE_THEME_NOT_SUPPORTED);
        }

        if (! $this->settingsResolver->isOnlineCheckoutEnabled()) {
            throw PaymentGatewayException::notConfigured();
        }

        if ($order->order_status === 'cancelled') {
            throw PaymentVerificationException::orderNotPayable();
        }

        if ((float) $order->amount <= 0) {
            throw PaymentVerificationException::orderNotPayable();
        }
    }
}
