<?php

namespace App\Services\Payments\Gateways;

use App\Models\Setting;
use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Services\Payments\DTOs\CreatePaymentOrderData;
use App\Services\Payments\DTOs\CreatePaymentOrderResult;
use App\Services\Payments\DTOs\VerifyPaymentData;
use App\Services\Payments\DTOs\VerifyPaymentResult;
use App\Services\Payments\Exceptions\PaymentGatewayException;
use App\Services\Payments\Exceptions\PaymentVerificationException;
use Razorpay\Api\Api;
use Throwable;

class RazorpayGateway implements PaymentGatewayInterface
{
    public function slug(): string
    {
        return 'razorpay';
    }

    public function displayName(): string
    {
        return 'Razorpay';
    }

    public function isConfigured(): bool
    {
        return Setting::isRazorpayConfigured();
    }

    public function createOrder(CreatePaymentOrderData $data): CreatePaymentOrderResult
    {
        if (! $this->isConfigured()) {
            throw PaymentGatewayException::notConfigured();
        }

        $amountInPaise = (int) round($data->amount * 100);

        try {
            $api = $this->api();
            $order = $api->order->create([
                'receipt' => $data->orderUuid,
                'amount' => $amountInPaise,
                'currency' => $data->currency,
                'notes' => [
                    'order_id' => (string) $data->orderId,
                    'order_uuid' => $data->orderUuid,
                ],
            ]);

            $payload = $order->toArray();

            return new CreatePaymentOrderResult(
                gatewayOrderId: (string) $payload['id'],
                amount: $data->amount,
                currency: $data->currency,
                amountInSmallestUnit: $amountInPaise,
                gateway: $this->slug(),
                rawResponse: $payload,
            );
        } catch (PaymentGatewayException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw PaymentGatewayException::unreachable($e);
        }
    }

    public function verifyPayment(VerifyPaymentData $data): VerifyPaymentResult
    {
        if (! $this->isConfigured()) {
            throw PaymentGatewayException::notConfigured();
        }

        $secret = Setting::getRazorpayKeySecret();
        $payload = $data->gatewayOrderId.'|'.$data->gatewayPaymentId;
        $expectedSignature = hash_hmac('sha256', $payload, (string) $secret);

        if (! hash_equals($expectedSignature, $data->signature)) {
            throw PaymentVerificationException::invalidSignature();
        }

        try {
            $api = $this->api();
            $payment = $api->payment->fetch($data->gatewayPaymentId);
            $paymentArray = $payment->toArray();

            $paidAmount = ((int) ($paymentArray['amount'] ?? 0)) / 100;
            $currency = (string) ($paymentArray['currency'] ?? $data->expectedCurrency);

            if (abs($paidAmount - $data->expectedAmount) > 0.01) {
                throw PaymentVerificationException::amountMismatch();
            }

            if (strtoupper($currency) !== strtoupper($data->expectedCurrency)) {
                throw PaymentVerificationException::currencyMismatch();
            }

            if (($paymentArray['order_id'] ?? null) !== $data->gatewayOrderId) {
                throw PaymentVerificationException::orderNotPayable();
            }

            return new VerifyPaymentResult(
                gatewayOrderId: $data->gatewayOrderId,
                gatewayPaymentId: $data->gatewayPaymentId,
                signature: $data->signature,
                amount: $paidAmount,
                currency: $currency,
                gateway: $this->slug(),
                rawResponse: $paymentArray,
            );
        } catch (PaymentVerificationException|PaymentGatewayException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw PaymentGatewayException::unreachable($e);
        }
    }

    public function frontendPublicConfig(): array
    {
        return [
            'key_id' => Setting::getRazorpayKeyId(),
        ];
    }

    private function api(): Api
    {
        return new Api(
            (string) Setting::getRazorpayKeyId(),
            (string) Setting::getRazorpayKeySecret()
        );
    }
}
