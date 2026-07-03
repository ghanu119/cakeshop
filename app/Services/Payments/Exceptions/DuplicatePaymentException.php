<?php

namespace App\Services\Payments\Exceptions;

class DuplicatePaymentException extends PaymentException
{
    public static function alreadyPaid(): self
    {
        return new self(self::CODE_ORDER_ALREADY_PAID);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
