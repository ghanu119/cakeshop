<?php

namespace App\Messaging\Exceptions;

use RuntimeException;
use Throwable;

class MessageDeliveryException extends RuntimeException
{
    public const REASON_DISABLED = 'disabled';

    public const REASON_INVALID_NUMBER = 'invalid_number';

    public const REASON_UNDELIVERABLE = 'undeliverable';

    public const REASON_PROVIDER_ERROR = 'provider_error';

    public function __construct(
        public readonly string $reason = self::REASON_PROVIDER_ERROR,
        string $message = '',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message !== '' ? $message : $reason, 0, $previous);
    }

    /**
     * Whether the failure indicates the recipient likely cannot receive the
     * message (not on WhatsApp / bad number) rather than a transient error.
     */
    public function isRecipientProblem(): bool
    {
        return in_array($this->reason, [
            self::REASON_INVALID_NUMBER,
            self::REASON_UNDELIVERABLE,
        ], true);
    }
}
