<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderService;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFulfillmentTest extends TestCase
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

    public function test_order_place_url_uses_product_slug(): void
    {
        $product = $this->simpleProduct();

        $url = route('order.place', $product);

        $this->assertStringContainsString('/order/product/'.$product->slug, $url);
        $this->assertStringNotContainsString('/order/product/'.$product->id, $url);

        $this->get($url)->assertOk();
    }

    public function test_checkout_shows_order_type_when_theme_setting_is_null(): void
    {
        Setting::set('theme', null);
        Setting::flushCache();

        $product = $this->simpleProduct();

        $response = $this->get(route('order.place', $product));

        $response->assertOk();
        $response->assertSee(__('Order type'), false);
        $response->assertSee(__('Take away'), false);
        $response->assertSee(__('Deliver'), false);
        $response->assertDontSee(__('Quantity'), false);
    }

    public function test_better_buns_takeaway_order_defaults_quantity_one(): void
    {
        Setting::set('theme', 'better-buns');
        Setting::flushCache();

        $product = $this->simpleProduct();

        $response = $this->post(route('order.store', $product), array_merge($this->validOrderPayload(), [
            'fulfillment_type' => 'takeaway',
            'quantity' => 1,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'product_id' => $product->id,
            'quantity' => 1,
            'fulfillment_type' => 'takeaway',
            'delivery_address' => null,
        ]);
    }

    public function test_better_buns_delivery_requires_address(): void
    {
        Setting::set('theme', 'better-buns');
        Setting::flushCache();

        $product = $this->simpleProduct();

        $response = $this->post(route('order.store', $product), array_merge($this->validOrderPayload(), [
            'fulfillment_type' => 'delivery',
        ]));

        $response->assertSessionHasErrors('delivery_address');
    }

    public function test_better_buns_delivery_persists_address(): void
    {
        Setting::set('theme', 'better-buns');
        Setting::flushCache();

        $product = $this->simpleProduct();
        $address = '42 Baker Street, Downtown';

        $response = $this->post(route('order.store', $product), array_merge($this->validOrderPayload(), [
            'fulfillment_type' => 'delivery',
            'delivery_address' => $address,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'product_id' => $product->id,
            'fulfillment_type' => 'delivery',
            'delivery_address' => $address,
        ]);
    }

    public function test_warm_theme_requires_fulfillment_and_defaults_quantity_one(): void
    {
        Setting::set('theme', 'warm');
        Setting::flushCache();

        $product = $this->simpleProduct();

        $response = $this->post(route('order.store', $product), array_merge($this->validOrderPayload(), [
            'fulfillment_type' => 'takeaway',
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'product_id' => $product->id,
            'quantity' => 1,
            'fulfillment_type' => 'takeaway',
        ]);
    }

    public function test_lumiere_theme_keeps_quantity_without_fulfillment_fields(): void
    {
        Setting::set('theme', 'lumiere');
        Setting::flushCache();

        $product = $this->simpleProduct();

        $response = $this->post(route('order.store', $product), array_merge($this->validOrderPayload(), [
            'quantity' => 2,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'product_id' => $product->id,
            'quantity' => 2,
            'fulfillment_type' => 'takeaway',
        ]);
    }

    public function test_admin_order_show_displays_fulfillment_for_admin(): void
    {
        $admin = $this->adminUser();
        $order = Order::factory()
            ->verified()
            ->deliveryFulfillment('123 Main St')
            ->create();

        $response = $this->actingAs($admin)->get(route('admin.orders.show', $order));

        $response->assertOk();
        $response->assertSee(__('Delivery'), false);
        $response->assertSee('123 Main St', false);
    }

    public function test_kitchen_order_show_view_does_not_include_fulfillment_partial(): void
    {
        $path = resource_path('views/kitchen/orders/show.blade.php');
        $contents = file_get_contents($path);

        $this->assertStringNotContainsString('_fulfillment-highlight', $contents);
        $this->assertStringNotContainsString('delivery_address', $contents);
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        return $admin;
    }

    private function kitchenUser(): User
    {
        $kitchen = User::factory()->create(['email_verified_at' => now()]);
        $kitchen->assignRole('Kitchen');

        return $kitchen;
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
