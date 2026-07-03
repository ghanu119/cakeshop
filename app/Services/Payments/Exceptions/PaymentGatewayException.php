<?php

namespace App\Services\Payments\Exceptions;

class PaymentGatewayException extends PaymentException
{
    public static function notConfigured(): self
    {
        return new self(self::CODE_GATEWAY_NOT_CONFIGURED);
    }

    public static function unreachable(?\Throwable $previous = null): self
    {
        return new self(self::CODE_GATEWAY_UNREACHABLE, previous: $previous);
    }
}
