<?php

namespace App\Services\Payments\DTOs;

readonly class VerifyPaymentResult
{
    /**
     * @param  array<string, mixed>  $rawResponse
     */
    public function __construct(
        public string $gatewayOrderId,
        public string $gatewayPaymentId,
        public string $signature,
        public float $amount,
        public string $currency,
        public string $gateway,
        public array $rawResponse = [],
    ) {}
}
