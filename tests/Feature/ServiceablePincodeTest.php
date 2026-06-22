<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ServiceablePincode;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderService;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceablePincodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        Setting::set('timezone', 'Asia/Kolkata');
        Setting::set('order_max_future_days', '7');
        Setting::set('order_min_hours_before_delivery', '4');
        Setting::set('theme', 'better-buns');
        Setting::flushCache();

        ServiceablePincode::factory()->create([
            'pincode' => '360004',
            'locality' => 'Kalawad Road',
            'city' => 'Rajkot',
            'state' => 'Gujarat',
            'is_active' => true,
        ]);
    }

    public function test_check_pincode_endpoint_returns_serviceable(): void
    {
        $response = $this->postJson(route('order.pincode.check'), [
            'pincode' => '360004',
        ]);

        $response->assertOk()
            ->assertJson([
                'serviceable' => true,
                'locality' => 'Kalawad Road',
                'city' => 'Rajkot',
            ]);
    }

    public function test_check_pincode_endpoint_returns_not_serviceable(): void
    {
        $response = $this->postJson(route('order.pincode.check'), [
            'pincode' => '400001',
        ]);

        $response->assertOk()
            ->assertJson([
                'serviceable' => false,
            ]);
    }

    public function test_delivery_order_rejected_without_pincode(): void
    {
        $product = $this->simpleProduct();

        $response = $this->post(route('order.store', $product), array_merge($this->validOrderPayload(), [
            'fulfillment_type' => 'delivery',
            'delivery_address' => '42 Baker Street',
        ]));

        $response->assertSessionHasErrors('delivery_pincode');
    }

    public function test_delivery_order_rejected_with_invalid_pincode(): void
    {
        $product = $this->simpleProduct();

        $response = $this->post(route('order.store', $product), array_merge($this->validOrderPayload(), [
            'fulfillment_type' => 'delivery',
            'delivery_pincode' => '400001',
            'delivery_address' => '42 Baker Street',
        ]));

        $response->assertSessionHasErrors('delivery_pincode');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_delivery_order_accepted_with_valid_pincode(): void
    {
        $product = $this->simpleProduct();
        $address = '42 Baker Street, Kalawad Road';

        $response = $this->post(route('order.store', $product), array_merge($this->validOrderPayload(), [
            'fulfillment_type' => 'delivery',
            'delivery_pincode' => '360004',
            'delivery_address' => $address,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'product_id' => $product->id,
            'fulfillment_type' => 'delivery',
            'delivery_pincode' => '360004',
            'delivery_address' => $address,
        ]);
    }

    public function test_takeaway_order_skips_pincode(): void
    {
        $product = $this->simpleProduct();

        $response = $this->post(route('order.store', $product), array_merge($this->validOrderPayload(), [
            'fulfillment_type' => 'takeaway',
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'product_id' => $product->id,
            'fulfillment_type' => 'takeaway',
            'delivery_pincode' => null,
        ]);
    }

    public function test_admin_can_manage_serviceable_pincodes(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('admin.serviceable-pincodes.index'))
            ->assertOk()
            ->assertSee('360004', false);

        $this->actingAs($admin)
            ->post(route('admin.serviceable-pincodes.store'), [
                'pincode' => '360001',
                'locality' => 'Rajkot GPO',
                'city' => 'Rajkot',
                'state' => 'Gujarat',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.serviceable-pincodes.index'));

        $this->assertDatabaseHas('serviceable_pincodes', [
            'pincode' => '360001',
            'locality' => 'Rajkot GPO',
        ]);
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        return $admin;
    }

    private function simpleProduct(): Product
    {
        $category = Category::factory()->create();

        return Product::factory()->create([
            'category_id' => $category->id,
            'price' => 500,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validOrderPayload(): array
    {
        $rules = app(OrderService::class)->deliveryAtRules();
        $delivery = Carbon::parse($rules['after']->copy()->addHours(2), 'UTC')
            ->setTimezone($rules['timezone'])
            ->format('Y-m-d\TH:i');

        return [
            'guest_name' => 'Test Customer',
            'guest_email' => 'customer@example.com',
            'guest_phone' => '9876543210',
            'quantity' => 1,
            'delivery_at' => $delivery,
        ];
    }
}
