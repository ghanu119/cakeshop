<?php

namespace App\Services\Payments\DTOs;

readonly class VerifyPaymentData
{
    public function __construct(
        public string $gatewayOrderId,
        public string $gatewayPaymentId,
        public string $signature,
        public float $expectedAmount,
        public string $expectedCurrency,
    ) {}
}
