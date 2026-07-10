<?php

namespace Tests\Feature;

use App\Livewire\Account\AuthModal;
use App\Messaging\Contracts\MessagingGateway;
use App\Messaging\Exceptions\MessageDeliveryException;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderService;
use App\Support\AuthGuards;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Fakes\FakeMessagingGateway;
use Tests\TestCase;

class WhatsAppOtpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        Setting::set('timezone', 'Asia/Kolkata');
        Setting::set('order_max_future_days', '7');
        Setting::set('order_min_hours_before_delivery', '4');
        Setting::flushCache();
    }

    private function fakeWhatsApp(): FakeMessagingGateway
    {
        config([
            'services.whatsapp.enabled' => true,
            'services.whatsapp.phone_number_id' => 'PNID',
            'services.whatsapp.access_token' => 'token',
            'services.whatsapp.default_country_code' => '91',
        ]);

        $fake = new FakeMessagingGateway;
        $this->app->instance(MessagingGateway::class, $fake);

        return $fake;
    }

    private function validDeliveryAt(): string
    {
        $rules = app(OrderService::class)->deliveryAtRules();

        return Carbon::parse($rules['after']->copy()->addHours(2), 'UTC')
            ->setTimezone($rules['timezone'])
            ->format('Y-m-d\TH:i');
    }

    private function orderPayload(array $overrides = []): array
    {
        return array_merge([
            'guest_name' => 'Buyer',
            'guest_email' => 'buyer@example.com',
            'guest_phone' => '9876501234',
            'quantity' => 1,
            'delivery_at' => $this->validDeliveryAt(),
            'fulfillment_type' => 'takeaway',
        ], $overrides);
    }

    public function test_new_customer_can_register_via_whatsapp_otp_without_email(): void
    {
        $fake = $this->fakeWhatsApp();

        $component = Livewire::test(AuthModal::class)
            ->set('channel', 'whatsapp')
            ->set('phone', '9558517749')
            ->call('sendOtp')
            ->assertHasNoErrors()
            ->assertSet('step', 'otp');

        $component->set('code', $fake->lastOtpCode())
            ->call('verifyOtp')
            ->assertSet('step', 'profile')
            ->set('name', 'Phone Only Customer')
            ->set('email', '')
            ->call('completeProfile');

        $this->assertAuthenticated(AuthGuards::CUSTOMER);

        $user = User::where('phone', '9558517749')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email);
        $this->assertNotNull($user->whatsapp_verified_at);
    }

    public function test_whatsapp_sign_up_rejects_invalid_optional_email(): void
    {
        $fake = $this->fakeWhatsApp();

        Livewire::test(AuthModal::class)
            ->set('channel', 'whatsapp')
            ->set('phone', '9558517751')
            ->call('sendOtp')
            ->assertSet('step', 'otp')
            ->set('code', $fake->lastOtpCode())
            ->call('verifyOtp')
            ->assertSet('step', 'profile')
            ->set('name', 'Wa Customer')
            ->set('email', 'not-an-email')
            ->call('completeProfile')
            ->assertHasErrors('email');

        $this->assertGuest(AuthGuards::CUSTOMER);
    }

    public function test_new_customer_can_register_via_whatsapp_otp(): void
    {
        $fake = $this->fakeWhatsApp();

        $component = Livewire::test(AuthModal::class)
            ->set('channel', 'whatsapp')
            ->set('phone', '9558517748')
            ->call('sendOtp')
            ->assertHasNoErrors()
            ->assertSet('step', 'otp');

        $code = $fake->lastOtpCode();
        $this->assertNotNull($code);

        $component->set('code', $code)
            ->call('verifyOtp')
            ->assertSet('step', 'profile')
            ->set('name', 'Wa Customer')
            ->set('email', 'wa@example.com')
            ->call('completeProfile');

        $this->assertAuthenticated(AuthGuards::CUSTOMER);

        $user = User::where('phone', '9558517748')->first();
        $this->assertNotNull($user);
        $this->assertSame('wa@example.com', $user->email);
        $this->assertNotNull($user->whatsapp_verified_at);
        $this->assertTrue($user->isWhatsAppVerified());
        // Email was only typed in during WhatsApp signup — never OTP-verified.
        $this->assertNull($user->email_verified_at);
        $this->assertFalse($user->isEmailVerified());
    }

    public function test_existing_phone_only_customer_logs_in_via_whatsapp(): void
    {
        $fake = $this->fakeWhatsApp();

        $customer = User::factory()->customer()->create([
            'email' => null,
            'phone' => '9111222333',
        ]);

        $component = Livewire::test(AuthModal::class)
            ->set('channel', 'whatsapp')
            ->set('phone', '9111222333')
            ->call('sendOtp')
            ->assertSet('step', 'otp');

        $component->set('code', $fake->lastOtpCode())
            ->call('verifyOtp');

        $this->assertAuthenticatedAs($customer, AuthGuards::CUSTOMER);
    }

    public function test_whatsapp_delivery_failure_shows_fallback_message(): void
    {
        $fake = $this->fakeWhatsApp();
        $fake->throwReason = MessageDeliveryException::REASON_UNDELIVERABLE;

        Livewire::test(AuthModal::class)
            ->set('channel', 'whatsapp')
            ->set('phone', '9558517748')
            ->call('sendOtp')
            ->assertHasErrors('phone')
            ->assertSet('step', 'contact');

        $this->assertGuest(AuthGuards::CUSTOMER);
    }

    public function test_whatsapp_sign_in_rejects_invalid_phone_number(): void
    {
        $this->fakeWhatsApp();

        Livewire::test(AuthModal::class)
            ->set('channel', 'whatsapp')
            ->set('phone', '12345')
            ->call('sendOtp')
            ->assertHasErrors('phone')
            ->assertSet('step', 'contact');

        $this->assertGuest(AuthGuards::CUSTOMER);
    }

    public function test_checkout_send_and_verify_whatsapp_otp(): void
    {
        $fake = $this->fakeWhatsApp();

        $this->postJson(route('order.checkout.send-otp'), [
            'channel' => 'whatsapp',
            'phone' => '9558517748',
        ])->assertOk()->assertJson(['channel' => 'whatsapp']);

        $this->postJson(route('order.checkout.verify-otp'), [
            'channel' => 'whatsapp',
            'phone' => '9558517748',
            'code' => $fake->lastOtpCode(),
        ])->assertOk()->assertJson(['verified' => true]);
    }

    public function test_checkout_whatsapp_send_failure_returns_email_fallback(): void
    {
        $fake = $this->fakeWhatsApp();
        $fake->throwReason = MessageDeliveryException::REASON_UNDELIVERABLE;

        $this->postJson(route('order.checkout.send-otp'), [
            'channel' => 'whatsapp',
            'phone' => '9558517748',
            'email' => 'fallback@example.com',
        ])->assertStatus(422)->assertJson(['fallback' => 'email']);
    }

    public function test_checkout_whatsapp_send_failure_without_email_has_no_fallback(): void
    {
        $fake = $this->fakeWhatsApp();
        $fake->throwReason = MessageDeliveryException::REASON_UNDELIVERABLE;

        $response = $this->postJson(route('order.checkout.send-otp'), [
            'channel' => 'whatsapp',
            'phone' => '9558517748',
        ])->assertStatus(422);

        $this->assertArrayNotHasKey('fallback', $response->json());
    }

    public function test_guest_can_place_order_with_whatsapp_otp_without_email(): void
    {
        $fake = $this->fakeWhatsApp();
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);

        $this->postJson(route('order.checkout.send-otp'), [
            'channel' => 'whatsapp',
            'phone' => '9558517750',
        ])->assertOk();

        $this->postJson(route('order.checkout.verify-otp'), [
            'channel' => 'whatsapp',
            'phone' => '9558517750',
            'code' => $fake->lastOtpCode(),
        ])->assertOk();

        $payload = $this->orderPayload([
            'guest_phone' => '9558517750',
        ]);
        unset($payload['guest_email']);

        $this->post(route('order.store', $product), $payload)->assertRedirect();

        $this->assertAuthenticated(AuthGuards::CUSTOMER);

        $customer = User::where('phone', '9558517750')->first();
        $this->assertNotNull($customer);

        $order = Order::latest('id')->first();
        $this->assertSame($customer->id, $order->user_id);
        $this->assertNull($order->guest_email);
    }

    public function test_guest_can_place_order_with_whatsapp_otp(): void
    {
        $fake = $this->fakeWhatsApp();
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);

        $this->postJson(route('order.checkout.send-otp'), [
            'channel' => 'whatsapp',
            'phone' => '9558517748',
        ])->assertOk();

        $this->postJson(route('order.checkout.verify-otp'), [
            'channel' => 'whatsapp',
            'phone' => '9558517748',
            'code' => $fake->lastOtpCode(),
        ])->assertOk();

        $this->post(route('order.store', $product), $this->orderPayload([
            'guest_email' => 'wacheckout@example.com',
            'guest_phone' => '9558517748',
        ]))->assertRedirect();

        $this->assertAuthenticated(AuthGuards::CUSTOMER);

        $customer = User::where('phone', '9558517748')->first();
        $this->assertNotNull($customer);
        $this->assertNotNull($customer->whatsapp_verified_at);

        $order = Order::latest('id')->first();
        $this->assertSame($customer->id, $order->user_id);
    }
}
