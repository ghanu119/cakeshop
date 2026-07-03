<?php

namespace App\Services\Payments;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class PaymentManager
{
    public function __construct(
        private Container $container,
        private PaymentSettingsResolver $settingsResolver,
    ) {}

    public function defaultDriver(): ?string
    {
        return $this->settingsResolver->activeGatewaySlug();
    }

    public function driver(?string $name = null): PaymentGatewayInterface
    {
        $name = $name ?? $this->defaultDriver();

        $class = config("payment.gateways.{$name}");

        if ($class === null || ! is_string($class)) {
            throw new InvalidArgumentException("Payment gateway [{$name}] is not configured.");
        }

        $gateway = $this->container->make($class);

        if (! $gateway instanceof PaymentGatewayInterface) {
            throw new InvalidArgumentException("Gateway [{$class}] must implement PaymentGatewayInterface.");
        }

        return $gateway;
    }

    public function isOnlineCheckoutEnabled(): bool
    {
        return $this->settingsResolver->isOnlineCheckoutEnabled();
    }
}
