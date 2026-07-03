<?php

namespace App\Services\Payments\Contracts;

use App\Services\Payments\DTOs\CreatePaymentOrderData;
use App\Services\Payments\DTOs\CreatePaymentOrderResult;
use App\Services\Payments\DTOs\VerifyPaymentData;
use App\Services\Payments\DTOs\VerifyPaymentResult;

interface PaymentGatewayInterface
{
    public function slug(): string;

    public function displayName(): string;

    public function isConfigured(): bool;

    public function createOrder(CreatePaymentOrderData $data): CreatePaymentOrderResult;

    public function verifyPayment(VerifyPaymentData $data): VerifyPaymentResult;

    /**
     * @return array<string, mixed>
     */
    public function frontendPublicConfig(): array;
}
