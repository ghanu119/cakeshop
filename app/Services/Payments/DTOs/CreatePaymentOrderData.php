<?php

namespace App\Services\Payments\DTOs;

readonly class CreatePaymentOrderData
{
    public function __construct(
        public int $orderId,
        public string $orderUuid,
        public float $amount,
        public string $currency,
        public ?string $customerName = null,
        public ?string $customerEmail = null,
        public ?string $customerPhone = null,
    ) {}
}
