<?php

namespace App\Services\Payments\Exceptions;

class PaymentVerificationException extends PaymentException
{
    public static function invalidSignature(): self
    {
        return new self(self::CODE_SIGNATURE_INVALID);
    }

    public static function amountMismatch(): self
    {
        return new self(self::CODE_AMOUNT_MISMATCH);
    }

    public static function currencyMismatch(): self
    {
        return new self(self::CODE_CURRENCY_MISMATCH);
    }

    public static function orderNotPayable(): self
    {
        return new self(self::CODE_ORDER_NOT_PAYABLE);
    }
}
