<?php

namespace App\Services\Payments\Exceptions;

use Exception;

class PaymentException extends Exception
{
    public const CODE_GATEWAY_NOT_CONFIGURED = 'gateway_not_configured';

    public const CODE_GATEWAY_UNREACHABLE = 'gateway_unreachable';

    public const CODE_SIGNATURE_INVALID = 'signature_invalid';

    public const CODE_AMOUNT_MISMATCH = 'amount_mismatch';

    public const CODE_CURRENCY_MISMATCH = 'currency_mismatch';

    public const CODE_ORDER_ALREADY_PAID = 'order_already_paid';

    public const CODE_ORDER_NOT_PAYABLE = 'order_not_payable';

    public const CODE_DUPLICATE_PAYMENT = 'duplicate_payment';

    public const CODE_THEME_NOT_SUPPORTED = 'theme_not_supported';

    public const CODE_SESSION_EXPIRED = 'session_expired';

    public const CODE_UNKNOWN = 'unknown';

    public function __construct(
        protected string $customerCode = self::CODE_UNKNOWN,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message !== '' ? $message : $customerCode, $code, $previous);
    }

    public function customerCode(): string
    {
        return $this->customerCode;
    }

    public function isRetryable(): bool
    {
        return match ($this->customerCode) {
            self::CODE_GATEWAY_NOT_CONFIGURED,
            self::CODE_ORDER_ALREADY_PAID,
            self::CODE_THEME_NOT_SUPPORTED => false,
            default => true,
        };
    }
}
