<?php

namespace Tests\Feature;

use App\Mail\CustomerLoginOtp;
use App\Models\Order;
use App\Models\Product;
use App\Support\AuthGuards;
use App\Models\Setting;
use App\Models\User;
use App\Services\CustomerAuthService;
use App\Services\OrderService;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AccountCheckoutTest extends TestCase
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

        config([
            'services.whatsapp.enabled' => false,
            'services.whatsapp.phone_number_id' => null,
            'services.whatsapp.access_token' => null,
        ]);
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

    private function verifyGuestEmail(string $email): void
    {
        Mail::fake();
        app(CustomerAuthService::class)->sendOtp($email);

        $code = null;
        Mail::assertSent(CustomerLoginOtp::class, function (CustomerLoginOtp $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        app(CustomerAuthService::class)->verifyOtp($email, $code);
    }

    public function test_guest_can_access_order_form(): void
    {
        $product = Product::factory()->create(['status' => 'active']);

        $this->get(route('order.place', $product))
            ->assertOk();
    }

    public function test_guest_cannot_place_order_without_otp_verification(): void
    {
        $product = Product::factory()->create(['status' => 'active']);

        $this->post(route('order.store', $product), $this->orderPayload())
            ->assertSessionHasErrors('guest_email');

        $this->assertGuest();
        $this->assertSame(0, Order::count());
    }

    public function test_guest_otp_checkout_creates_customer_and_order(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);
        $email = 'checkout@example.com';
        $this->verifyGuestEmail($email);

        $this->post(route('order.store', $product), $this->orderPayload([
            'guest_email' => $email,
        ]))->assertRedirect();

        $this->assertAuthenticated(AuthGuards::CUSTOMER);
        $customer = User::where('email', $email)->first();
        $this->assertNotNull($customer);

        $order = Order::latest('id')->first();
        $this->assertSame($customer->id, $order->user_id);
        $this->assertSame('Buyer', $order->guest_name);
        $this->assertSame($email, $order->guest_email);
    }

    public function test_checkout_verify_otp_then_legacy_store_creates_order(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);
        $email = 'checkout@example.com';

        Mail::fake();
        app(CustomerAuthService::class)->sendOtp($email);

        $code = null;
        Mail::assertSent(CustomerLoginOtp::class, function (CustomerLoginOtp $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        $this->postJson(route('order.checkout.verify-otp'), [
            'email' => $email,
            'code' => $code,
            'guest_name' => 'Buyer',
            'guest_phone' => '9876501234',
        ])
            ->assertOk()
            ->assertJsonPath('authenticated', true);

        $this->post(route('order.store', $product), $this->orderPayload([
            'guest_email' => $email,
        ]))->assertRedirect();

        $this->assertAuthenticated(AuthGuards::CUSTOMER);

        $customer = User::where('email', $email)->first();
        $this->assertNotNull($customer);

        $order = Order::latest('id')->first();
        $this->assertSame($customer->id, $order->user_id);
        $this->assertSame('Buyer', $order->guest_name);
        $this->assertSame($email, $order->guest_email);
    }

    public function test_logged_in_customer_can_access_order_form(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create(['status' => 'active']);

        $this->actingAs($customer)
            ->get(route('order.place', $product))
            ->assertOk();
    }

    public function test_order_form_prefills_customer_contact_details(): void
    {
        $customer = User::factory()->customer()->create([
            'name' => 'Roanna Jefferson',
            'email' => 'roanna@example.com',
            'phone' => '8282938816',
        ]);
        $product = Product::factory()->create(['status' => 'active']);

        $response = $this->actingAs($customer)->get(route('order.place', $product));

        $response->assertOk();
        $response->assertSee('value="Roanna Jefferson"', false);
        $response->assertSee('value="8282938816"', false);
        $response->assertSee('value="roanna@example.com"', false);
        $response->assertSee(__('Who is this order for?'), false);
    }

    public function test_logged_in_customer_checkout_requires_email(): void
    {
        $customer = User::factory()->customer()->create([
            'name' => 'Roanna Jefferson',
            'email' => 'roanna@example.com',
            'phone' => '8282938816',
        ]);
        $product = Product::factory()->create(['status' => 'active']);

        $this->actingAs($customer)
            ->get(route('order.place', $product))
            ->assertOk()
            ->assertDontSee('(' . __('Optional') . ')', false);
    }

    public function test_logged_in_customer_can_order_for_someone_else(): void
    {
        $customer = User::factory()->customer()->create([
            'name' => 'Roanna Jefferson',
            'email' => 'roanna@example.com',
            'phone' => '8282938816',
        ]);
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);

        $this->actingAs($customer)
            ->post(route('order.store', $product), $this->orderPayload([
                'guest_name' => 'Priya Sharma',
                'guest_email' => 'priya@example.com',
                'guest_phone' => '9999888777',
            ]))
            ->assertRedirect();

        $order = Order::latest('id')->first();
        $this->assertSame($customer->id, $order->user_id);
        $this->assertSame('Priya Sharma', $order->guest_name);
        $this->assertSame('priya@example.com', $order->guest_email);
        $this->assertTrue($order->hasDistinctContactFromAccount());
    }

    public function test_customer_order_sets_user_id(): void
    {
        $customer = User::factory()->customer()->create([
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'phone' => '9876501234',
        ]);
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);

        $this->actingAs($customer)
            ->post(route('order.store', $product), $this->orderPayload())
            ->assertRedirect();

        $order = Order::latest('id')->first();
        $this->assertSame($customer->id, $order->user_id);
        $this->assertSame('Buyer', $order->guest_name);
    }
}
