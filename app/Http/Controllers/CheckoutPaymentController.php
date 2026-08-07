<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinalizeCheckoutRequest;
use App\Http\Requests\FinalizeFreeCheckoutRequest;
use App\Http\Requests\PlaceOrderRequest;
use App\Models\Product;
use App\Services\Payments\CheckoutPaymentService;
use App\Services\Payments\Exceptions\PaymentException;
use App\Services\Payments\PaymentErrorMapper;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CheckoutPaymentController extends Controller
{
    public function __construct(
        private CheckoutPaymentService $checkoutPaymentService,
        private PaymentErrorMapper $paymentErrorMapper,
    ) {}

    public function prepare(PlaceOrderRequest $request, Product $product): JsonResponse
    {
        if (! $product->isActive()) {
            abort(404);
        }

        try {
            $payload = $this->checkoutPaymentService->prepare($product, $request->validated());

            return response()->json(array_merge($payload, [
                'success' => true,
            ]));
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function finalize(FinalizeCheckoutRequest $request): JsonResponse
    {
        try {
            $checkoutReference = (string) $request->validated('checkout_reference');

            $order = $this->checkoutPaymentService->finalize(
                $checkoutReference,
                (string) $request->validated('razorpay_order_id'),
                (string) $request->validated('razorpay_payment_id'),
                (string) $request->validated('razorpay_signature'),
            );

            return response()->json([
                'success' => true,
                'message' => __('payments.success.verified'),
                'retryable' => false,
                'redirect_url' => route('order.confirm', $order),
                'order_uuid' => $order->uuid,
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function finalizeFree(FinalizeFreeCheckoutRequest $request): JsonResponse
    {
        try {
            $order = $this->checkoutPaymentService->finalizeFreeOrder(
                (string) $request->validated('checkout_reference'),
            );

            return response()->json([
                'success' => true,
                'message' => __('payments.success.verified'),
                'retryable' => false,
                'redirect_url' => route('order.confirm', $order),
                'order_uuid' => $order->uuid,
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    private function errorResponse(Throwable $e): JsonResponse
    {
        if (! $e instanceof PaymentException) {
            report($e);
        }

        $mapped = $this->paymentErrorMapper->map($e);

        $status = match ($mapped['code']) {
            PaymentException::CODE_GATEWAY_NOT_CONFIGURED,
            PaymentException::CODE_GATEWAY_UNREACHABLE,
            PaymentException::CODE_UNKNOWN => Response::HTTP_SERVICE_UNAVAILABLE,
            PaymentException::CODE_THEME_NOT_SUPPORTED => Response::HTTP_FORBIDDEN,
            default => Response::HTTP_UNPROCESSABLE_ENTITY,
        };

        return response()->json([
            'success' => false,
            'message' => $mapped['message'],
            'retryable' => $mapped['retryable'],
            'code' => $mapped['code'],
        ], $status);
    }
}
