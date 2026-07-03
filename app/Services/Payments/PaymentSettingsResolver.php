<?php

namespace App\Services\Payments;

use App\Models\Setting;
use Throwable;

class PaymentSettingsResolver
{
    public function activeGatewaySlug(): string
    {
        $slug = Setting::getPaymentGateway();

        if (array_key_exists($slug, config('payment.gateways', []))) {
            return $slug;
        }

        return (string) config('payment.default', 'razorpay');
    }

    public function isRazorpayConfigured(): bool
    {
        return Setting::isRazorpayConfigured();
    }

    public function isOnlineCheckoutEnabled(): bool
    {
        return $this->activeGatewaySlug() === 'razorpay' && $this->isRazorpayConfigured();
    }

    public function applyGatewayConfig(): void
    {
        $config = Setting::razorpayConfig();

        if ($config === null) {
            return;
        }

        config([
            'payment.credentials.razorpay.key_id' => $config['key_id'],
            'payment.credentials.razorpay.key_secret' => $config['key_secret'],
        ]);
    }

    /**
     * @return array{enabled: bool, gateway: string, key_id: ?string}
     */
    public function frontendConfig(): array
    {
        return [
            'enabled' => $this->isOnlineCheckoutEnabled(),
            'gateway' => $this->activeGatewaySlug(),
            'key_id' => Setting::getRazorpayKeyId(),
        ];
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function testRazorpayConnection(): array
    {
        if (! $this->isRazorpayConfigured()) {
            return [
                'success' => false,
                'message' => __('Razorpay is not fully configured. Please enter Key ID and Key Secret.'),
            ];
        }

        try {
            $api = new \Razorpay\Api\Api(
                Setting::getRazorpayKeyId(),
                Setting::getRazorpayKeySecret()
            );

            $api->order->create([
                'receipt' => 'settings_test_'.time(),
                'amount' => 100,
                'currency' => Setting::get('currency') ?: 'INR',
            ]);

            return [
                'success' => true,
                'message' => __('Razorpay connection successful.'),
            ];
        } catch (Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => __('Could not connect to Razorpay. Please check your credentials and try again.'),
            ];
        }
    }
}
