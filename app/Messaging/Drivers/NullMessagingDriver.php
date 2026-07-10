<?php

namespace App\Messaging\Drivers;

use App\Messaging\Contracts\MessagingGateway;

/**
 * Safe no-op gateway used when messaging is disabled/misconfigured or in tests.
 */
class NullMessagingDriver implements MessagingGateway
{
    public function isEnabled(): bool
    {
        return false;
    }

    public function sendOtp(string $phone, string $code): void
    {
        // Intentionally no-op.
    }

    public function sendTemplate(string $phone, string $template, string $lang, array $bodyParams = []): void
    {
        // Intentionally no-op.
    }
}
