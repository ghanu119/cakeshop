<?php

namespace Tests\Feature;

use App\Mail\CustomerLoginOtp;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ServiceablePincode;
use App\Models\Setting;
use App\Models\VariantOptionValue;
use App\Services\CustomerAuthService;
use App\Services\OrderService;
use App\Services\ProductVariantService;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\VariantOptionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderDeliveryChargeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        Setting::set('timezone', 'Asia/Kolkata');
        Setting::set('order_max_future_days', '7');
        Setting::set('order_min_hours_before_delivery', '4');
        Setting::set('default_delivery_charge', '0');
        Setting::flushCache();

        ServiceablePincode::factory()->create([
            'pincode' => '360004',
            'is_active' => true,
        ]);
    }

    private function validDeliveryAt(): string
    {
        $rules = app(OrderService::class)->deliveryAtRules();

        return Carbon::parse($rules['after']->copy()->addHours(2), 'UTC')
            ->setTimezone($rules['timezone'])
            ->format('Y-m-d\TH:i');
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

    private function orderPayload(array $overrides = []): array
    {
        return array_merge([
            'guest_name' => 'Delivery Buyer',
            'guest_email' => 'deliverybuyer@example.com',
            'guest_phone' => '9876501234',
            'quantity' => 1,
            'delivery_at' => $this->validDeliveryAt(),
            'fulfillment_type' => 'takeaway',
        ], $overrides);
    }

    private function placeOrder(Product $product, array $overrides = []): Order
    {
        $this->verifyGuestEmail('deliverybuyer@example.com');

        $this->post(route('order.store', $product), $this->orderPayload($overrides));

        return Order::latest('id')->first();
    }

    /**
     * @param  array<int, float>  $weightPrices
     */
    private function createVariantProduct(array $weightPrices): Product
    {
        $this->seed(VariantOptionSeeder::class);

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'status' => 'active',
        ]);

        $variants = [];
        foreach ($weightPrices as $grams => $price) {
            $weight = VariantOptionValue::query()->forTypeSlug('weight')->where('grams', $grams)->firstOrFail();
            $variants[] = ['variant_option_value_id' => $weight->id, 'price' => $price];
        }

        app(ProductVariantService::class)->syncVariants($product, $variants);

        return $product->fresh();
    }

    private function variantForWeight(Product $product, int $grams): ProductVariant
    {
        return ProductVariant::query()
            ->where('product_id', $product->id)
            ->whereHas('selections.value', fn ($q) => $q->where('grams', $grams))
            ->firstOrFail();
    }

    private function setWeightDeliveryCharge(int $grams, ?float $charge): void
    {
        VariantOptionValue::query()->forTypeSlug('weight')->where('grams', $grams)->update(['delivery_charge' => $charge]);
    }

    public function test_delivery_order_for_weighted_variant_uses_that_weights_own_charge(): void
    {
        $product = $this->createVariantProduct([
            500 => 799,
            1000 => 1499,
        ]);
        $this->setWeightDeliveryCharge(1000, 50);
        $variant1kg = $this->variantForWeight($product, 1000);

        $order = $this->placeOrder($product, [
            'fulfillment_type' => 'delivery',
            'delivery_pincode' => '360004',
            'delivery_address' => '42 Baker Street',
            'product_variant_id' => $variant1kg->id,
        ]);

        $this->assertNotNull($order);
        $this->assertSame(50.0, (float) $order->delivery_charge);
        $this->assertSame(1499.0 + 50.0, (float) $order->amount);
    }

    public function test_delivery_order_falls_back_to_default_setting_when_weight_has_no_charge(): void
    {
        Setting::set('default_delivery_charge', '20');
        Setting::flushCache();

        $product = $this->createVariantProduct([
            500 => 799,
            1000 => 1499,
        ]);
        // 500g weight left with no explicit delivery_charge.
        $variant500g = $this->variantForWeight($product, 500);

        $order = $this->placeOrder($product, [
            'fulfillment_type' => 'delivery',
            'delivery_pincode' => '360004',
            'delivery_address' => '42 Baker Street',
            'product_variant_id' => $variant500g->id,
        ]);

        $this->assertNotNull($order);
        $this->assertSame(20.0, (float) $order->delivery_charge);
        $this->assertSame(799.0 + 20.0, (float) $order->amount);
    }

    public function test_weight_with_zero_charge_is_free_delivery_even_with_a_default_set(): void
    {
        Setting::set('default_delivery_charge', '20');
        Setting::flushCache();

        $product = $this->createVariantProduct([
            250 => 399,
        ]);
        $this->setWeightDeliveryCharge(250, 0);
        $variant = $this->variantForWeight($product, 250);

        $order = $this->placeOrder($product, [
            'fulfillment_type' => 'delivery',
            'delivery_pincode' => '360004',
            'delivery_address' => '42 Baker Street',
            'product_variant_id' => $variant->id,
        ]);

        $this->assertNotNull($order);
        $this->assertSame(0.0, (float) $order->delivery_charge);
    }

    public function test_takeaway_order_has_zero_delivery_charge(): void
    {
        $product = $this->createVariantProduct([
            500 => 799,
            1000 => 1499,
        ]);
        $this->setWeightDeliveryCharge(1000, 50);
        $variant1kg = $this->variantForWeight($product, 1000);

        $order = $this->placeOrder($product, [
            'fulfillment_type' => 'takeaway',
            'product_variant_id' => $variant1kg->id,
        ]);

        $this->assertNotNull($order);
        $this->assertSame(0.0, (float) $order->delivery_charge);
        $this->assertSame(1499.0, (float) $order->amount);
    }

    public function test_delivery_order_for_product_without_variants_uses_the_products_own_delivery_charge(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'status' => 'active',
            'price' => 600,
            'delivery_charge' => 35,
        ]);

        $order = $this->placeOrder($product, [
            'fulfillment_type' => 'delivery',
            'delivery_pincode' => '360004',
            'delivery_address' => '42 Baker Street',
        ]);

        $this->assertNotNull($order);
        $this->assertSame(35.0, (float) $order->delivery_charge);
        $this->assertSame(635.0, (float) $order->amount);
    }

    public function test_delivery_order_for_product_without_variants_or_own_charge_uses_default_setting(): void
    {
        Setting::set('default_delivery_charge', '15');
        Setting::flushCache();

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'status' => 'active',
            'price' => 600,
            'delivery_charge' => null,
        ]);

        $order = $this->placeOrder($product, [
            'fulfillment_type' => 'delivery',
            'delivery_pincode' => '360004',
            'delivery_address' => '42 Baker Street',
        ]);

        $this->assertNotNull($order);
        $this->assertSame(15.0, (float) $order->delivery_charge);
    }
}
