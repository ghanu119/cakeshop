<?php

namespace Tests\Unit;

use App\Messaging\Drivers\WhatsAppCloudDriver;
use App\Messaging\Exceptions\MessageDeliveryException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppCloudDriverTest extends TestCase
{
    private function config(array $overrides = []): array
    {
        return array_merge([
            'enabled' => true,
            'api_url' => 'https://graph.facebook.com/v21.0',
            'phone_number_id' => 'PNID',
            'access_token' => 'token',
            'default_country_code' => '91',
            'otp_template' => 'login_otp',
            'otp_template_lang' => 'en_US',
            'order_template' => 'order_update',
            'order_template_lang' => 'en_US',
            'test_mode' => false,
            'test_number' => null,
        ], $overrides);
    }

    public function test_test_mode_redirects_to_fixed_number(): void
    {
        Http::fake([
            '*' => Http::response(['messages' => [['id' => 'wamid.1']]], 200),
        ]);

        $driver = new WhatsAppCloudDriver($this->config([
            'test_mode' => true,
            'test_number' => '918511417739',
        ]));

        $driver->sendOtp('9558517748', '123456');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://graph.facebook.com/v21.0/PNID/messages'
                && $request['to'] === '918511417739';
        });
    }

    public function test_non_test_mode_prefixes_country_code(): void
    {
        Http::fake([
            '*' => Http::response(['messages' => [['id' => 'wamid.1']]], 200),
        ]);

        $driver = new WhatsAppCloudDriver($this->config());

        $driver->sendOtp('9558517748', '123456');

        Http::assertSent(fn ($request) => $request['to'] === '919558517748');
    }

    public function test_api_error_throws_undeliverable(): void
    {
        Http::fake([
            '*' => Http::response(['error' => ['code' => 131026, 'message' => 'undeliverable']], 400),
        ]);

        $driver = new WhatsAppCloudDriver($this->config());

        try {
            $driver->sendOtp('9558517748', '123456');
            $this->fail('Expected MessageDeliveryException was not thrown.');
        } catch (MessageDeliveryException $e) {
            $this->assertSame(MessageDeliveryException::REASON_UNDELIVERABLE, $e->reason);
        }
    }

    public function test_disabled_driver_throws(): void
    {
        $driver = new WhatsAppCloudDriver($this->config(['enabled' => false]));

        $this->expectException(MessageDeliveryException::class);

        $driver->sendOtp('9558517748', '123456');
    }

    public function test_order_template_includes_url_button_component(): void
    {
        Http::fake([
            '*' => Http::response(['messages' => [['id' => 'wamid.1']]], 200),
        ]);

        $driver = new WhatsAppCloudDriver($this->config([
            'order_url_button' => true,
            'order_url_button_index' => '0',
        ]));

        $driver->sendTemplate('9558517748', 'order_confirmation', 'en_US', ['A', 'ORD-1', 'Received'], 'order/confirm/abc-uuid');

        Http::assertSent(function ($request) {
            $components = $request['template']['components'] ?? [];

            return collect($components)->contains(function (array $component) {
                return ($component['type'] ?? null) === 'button'
                    && ($component['sub_type'] ?? null) === 'url'
                    && ($component['parameters'][0]['text'] ?? null) === 'order/confirm/abc-uuid';
            });
        });
    }
}
