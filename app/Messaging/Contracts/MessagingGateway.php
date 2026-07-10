<?php

namespace App\Messaging\Contracts;

use App\Messaging\Exceptions\MessageDeliveryException;

/**
 * Provider-agnostic outbound messaging gateway.
 *
 * Implementations deliver one-time codes and templated notifications through a
 * concrete provider (WhatsApp Cloud today; SMS or others later). Callers depend
 * only on this contract, so providers can be swapped via configuration.
 */
interface MessagingGateway
{
    /**
     * Whether the active provider is configured and enabled.
     */
    public function isEnabled(): bool;

    /**
     * Send a login one-time code.
     *
     * @throws MessageDeliveryException when the message cannot be delivered.
     */
    public function sendOtp(string $phone, string $code): void;

    /**
     * Send a pre-approved template message with ordered body parameters.
     *
     * @param  array<int, string|int|float>  $bodyParams
     *
     * @throws MessageDeliveryException when the message cannot be delivered.
     */
    public function sendTemplate(string $phone, string $template, string $lang, array $bodyParams = []): void;
}
