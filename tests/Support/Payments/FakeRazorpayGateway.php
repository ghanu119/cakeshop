<?php

namespace Tests\Support\Payments;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Services\Payments\DTOs\CreatePaymentOrderData;
use App\Services\Payments\DTOs\CreatePaymentOrderResult;
use App\Services\Payments\DTOs\VerifyPaymentData;
use App\Services\Payments\DTOs\VerifyPaymentResult;
use App\Services\Payments\Exceptions\PaymentVerificationException;

class FakeRazorpayGateway implements PaymentGatewayInterface
{
    public function __construct(
        private ?float $verifyAmount = null,
        private bool $validSignature = true,
    ) {}

    public function slug(): string
    {
        return 'razorpay';
    }

    public function displayName(): string
    {
        return 'Fake Razorpay';
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function createOrder(CreatePaymentOrderData $data): CreatePaymentOrderResult
    {
        return new CreatePaymentOrderResult(
            gatewayOrderId: 'order_fake_123',
            amount: $data->amount,
            currency: $data->currency,
            amountInSmallestUnit: (int) round($data->amount * 100),
            gateway: $this->slug(),
            rawResponse: ['id' => 'order_fake_123'],
        );
    }

    public function verifyPayment(VerifyPaymentData $data): VerifyPaymentResult
    {
        if (! $this->validSignature) {
            throw PaymentVerificationException::invalidSignature();
        }

        $amount = $this->verifyAmount ?? $data->expectedAmount;

        if (abs($amount - $data->expectedAmount) > 0.01) {
            throw PaymentVerificationException::amountMismatch();
        }

        return new VerifyPaymentResult(
            gatewayOrderId: $data->gatewayOrderId,
            gatewayPaymentId: $data->gatewayPaymentId,
            signature: $data->signature,
            amount: $amount,
            currency: $data->expectedCurrency,
            gateway: $this->slug(),
            rawResponse: ['id' => $data->gatewayPaymentId],
        );
    }

    public function frontendPublicConfig(): array
    {
        return ['key_id' => 'rzp_test_fake'];
    }
}
