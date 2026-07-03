<?php

namespace App\Services\Payments\DTOs;

readonly class CreatePaymentOrderResult
{
    /**
     * @param  array<string, mixed>  $rawResponse
     */
    public function __construct(
        public string $gatewayOrderId,
        public float $amount,
        public string $currency,
        public int $amountInSmallestUnit,
        public string $gateway,
        public array $rawResponse = [],
    ) {}
}
