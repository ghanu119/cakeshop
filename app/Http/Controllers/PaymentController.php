<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyPaymentRequest;
use App\Models\Order;
use App\Models\Setting;
use App\Services\OrderNotificationService;
use App\Services\Payments\DTOs\VerifyPaymentData;
use App\Services\Payments\Exceptions\PaymentException;
use App\Services\Payments\PaymentErrorMapper;
use App\Services\Payments\PaymentOrchestrator;
use App\Services\Payments\PaymentSettingsResolver;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentOrchestrator $paymentOrchestrator,
        private PaymentSettingsResolver $paymentSettingsResolver,
        private PaymentErrorMapper $paymentErrorMapper,
        private OrderNotificationService $orderNotificationService,
    ) {}

    public function initiate(Order $order): JsonResponse
    {
        try {
            $checkout = $this->paymentOrchestrator->initiateCheckout($order);
            $result = $checkout['result'];
            $publicConfig = $this->paymentSettingsResolver->frontendConfig();

            return response()->json([
                'success' => true,
                'key_id' => $publicConfig['key_id'],
                'gateway_order_id' => $result->gatewayOrderId,
                'amount' => $result->amountInSmallestUnit,
                'currency' => $result->currency,
                'order_uuid' => $order->uuid,
                'customer_name' => $order->guest_name,
                'customer_email' => $order->guest_email,
                'customer_phone' => $order->guest_phone,
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, $order);
        }
    }

    public function verify(VerifyPaymentRequest $request, Order $order): JsonResponse
    {
        try {
            if ($order->isPaymentVerified()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->paymentErrorMapper->messageForCode(PaymentException::CODE_ORDER_ALREADY_PAID),
                    'retryable' => false,
                    'order_uuid' => $order->uuid,
                ]);
            }

            $currency = (string) (Setting::get('currency') ?: config('payment.currency', 'INR'));

            $data = new VerifyPaymentData(
                gatewayOrderId: (string) $request->validated('razorpay_order_id'),
                gatewayPaymentId: (string) $request->validated('razorpay_payment_id'),
                signature: (string) $request->validated('razorpay_signature'),
                expectedAmount: (float) $order->amount,
                expectedCurrency: $currency,
            );

            $this->paymentOrchestrator->completeCheckout($order, $data);

            $order->refresh();

            if ($order->isPaymentVerified()) {
                $this->orderNotificationService->notifyPaymentVerified($order);
            }

            return response()->json([
                'success' => true,
                'message' => __('payments.success.verified'),
                'retryable' => false,
                'order_uuid' => $order->uuid,
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, $order);
        }
    }

    private function errorResponse(Throwable $e, Order $order): JsonResponse
    {
        if (! $e instanceof PaymentException) {
            report($e);
        }

        $mapped = $this->paymentErrorMapper->map($e);

        $status = match ($mapped['code']) {
            PaymentException::CODE_ORDER_ALREADY_PAID => Response::HTTP_OK,
            PaymentException::CODE_GATEWAY_NOT_CONFIGURED,
            PaymentException::CODE_GATEWAY_UNREACHABLE,
            PaymentException::CODE_UNKNOWN => Response::HTTP_SERVICE_UNAVAILABLE,
            PaymentException::CODE_THEME_NOT_SUPPORTED => Response::HTTP_FORBIDDEN,
            default => Response::HTTP_UNPROCESSABLE_ENTITY,
        };

        if ($mapped['code'] === PaymentException::CODE_ORDER_ALREADY_PAID) {
            return response()->json([
                'success' => true,
                'message' => $mapped['message'],
                'retryable' => false,
                'order_uuid' => $order->uuid,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $mapped['message'],
            'retryable' => $mapped['retryable'],
            'order_uuid' => $order->uuid,
        ], $status);
    }
}
