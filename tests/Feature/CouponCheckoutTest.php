<?php

namespace Tests\Feature;

use App\Mail\CustomerLoginOtp;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
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

class CouponCheckoutTest extends TestCase
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

    private function placeOrder(Product $product, array $overrides = []): Order
    {
        $this->verifyGuestEmail('buyer@example.com');

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

    public function test_auto_apply_coupon_discounts_order(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);

        Coupon::factory()->autoApply()->fixed(50)->create(['code' => 'AUTO50']);

        $order = $this->placeOrder($product);

        $this->assertSame(500.0, (float) $order->subtotal);
        $this->assertSame(50.0, (float) $order->discount_amount);
        $this->assertSame(450.0, (float) $order->amount);
        $this->assertSame('AUTO50', $order->coupon_code);
    }

    public function test_auto_apply_is_default_when_multiple_universal_coupons_exist(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);

        $autoCoupon = Coupon::factory()->autoApply()->fixed(50)->create([
            'code' => 'AUTO50',
            'label' => 'Auto 10% off',
        ]);
        Coupon::factory()->fixed(99)->create([
            'code' => 'ALL99',
            'label' => 'Apply On All User',
        ]);

        $response = $this->get(route('order.place', $product));

        $response->assertOk();
        $response->assertSee('Available offers', false);
        $response->assertDontSee('No coupon', false);
        $html = preg_replace('/\s+/', ' ', $response->getContent());
        $this->assertMatchesRegularExpression(
            '/id="coupon_code"[^>]*value="AUTO50"/',
            $html,
        );

        $order = $this->placeOrder($product, ['coupon_code' => 'AUTO50']);

        $this->assertSame(50.0, (float) $order->discount_amount);
        $this->assertSame($autoCoupon->id, $order->coupon_id);
        $this->assertSame('AUTO50', $order->coupon_code);
    }

    public function test_percentage_coupon_shows_max_discount_info_on_order_place(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 2799]);

        Coupon::factory()->autoApply()->percentage(10, 500)->create([
            'code' => 'AUTO10',
            'label' => 'Autoappply 10%',
        ]);
        Coupon::factory()->fixed(99)->create([
            'code' => 'ALL99',
            'label' => 'Apply On All User',
        ]);

        $response = $this->get(route('order.place', $product));

        $response->assertOk();
        $response->assertSee('coupon-max-discount-info', false);
        $response->assertSee('data-summary-max-discount-wrap', false);
        $response->assertSee('Maximum discount of', false);
        $response->assertSee('500.00', false);
    }

    public function test_manual_code_overrides_auto_apply(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);

        Coupon::factory()->autoApply()->fixed(50)->create(['code' => 'AUTO50']);
        Coupon::factory()->fixed(100)->create(['code' => 'VIP100']);

        $order = $this->placeOrder($product, ['coupon_code' => 'VIP100']);

        $this->assertSame(100.0, (float) $order->discount_amount);
        $this->assertSame('VIP100', $order->coupon_code);
    }

    public function test_no_coupon_is_default_when_no_auto_apply_offer_exists(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);

        Coupon::factory()->fixed(30)->create(['code' => 'OFF30', 'label' => 'Thirty off']);
        Coupon::factory()->fixed(80)->create(['code' => 'OFF80', 'label' => 'Eighty off']);

        $response = $this->get(route('order.place', $product));

        $response->assertOk();
        $response->assertDontSee('No coupon', false);
        $html = preg_replace('/\s+/', ' ', $response->getContent());
        $this->assertMatchesRegularExpression(
            '/id="coupon_code"[^>]*value=""/',
            $html,
        );

        $order = $this->placeOrder($product);

        $this->assertSame(0.0, (float) $order->discount_amount);
        $this->assertNull($order->coupon_id);
    }

    public function test_multiple_universal_coupons_use_selected_coupon_id(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);

        $couponA = Coupon::factory()->fixed(30)->create(['code' => 'OFF30', 'label' => 'Thirty off']);
        $couponB = Coupon::factory()->fixed(80)->create(['code' => 'OFF80', 'label' => 'Eighty off']);

        $order = $this->placeOrder($product, ['coupon_id' => $couponB->id]);

        $this->assertSame(80.0, (float) $order->discount_amount);
        $this->assertSame($couponB->id, $order->coupon_id);
    }

    public function test_user_scoped_coupon_requires_assigned_customer(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);
        $customer = User::factory()->create();
        $customer->assignRole('Customer');

        Coupon::factory()->fixed(40)->create([
            'code' => 'VIPONLY',
            'user_scope' => Coupon::USER_SCOPE_USERS,
        ])->users()->attach($customer);

        $this->verifyGuestEmail('buyer@example.com');

        $response = $this->post(route('order.store', $product), $this->orderPayload([
            'coupon_code' => 'VIPONLY',
        ]));

        $response->assertSessionHasErrors('coupon_code');
        $response->assertSessionHasErrors([
            'coupon_code' => __('Sign in to your account, then apply the code again.'),
        ]);
    }

    public function test_guest_validate_coupon_user_scoped_returns_sign_in_message(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);
        $customer = User::factory()->create();
        $customer->assignRole('Customer');

        Coupon::factory()->fixed(40)->create([
            'code' => 'VIPONLY',
            'user_scope' => Coupon::USER_SCOPE_USERS,
        ])->users()->attach($customer);

        $response = $this->postJson(route('order.product.validate-coupon', $product), [
            'quantity' => 1,
            'coupon_code' => 'VIPONLY',
        ]);

        $response->assertOk();
        $response->assertJson([
            'valid' => false,
            'reason' => 'sign_in_required',
        ]);
        $response->assertJsonFragment([
            'message' => __('Sign in to your account, then apply the code again.'),
        ]);
    }

    public function test_signed_in_wrong_customer_gets_account_message(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);
        $assignedCustomer = $this->createStorefrontCustomer(['email' => 'assigned@example.com']);
        $otherCustomer = $this->createStorefrontCustomer(['email' => 'other@example.com']);

        Coupon::factory()->fixed(40)->create([
            'code' => 'VIPONLY',
            'user_scope' => Coupon::USER_SCOPE_USERS,
        ])->users()->attach($assignedCustomer);

        $response = $this->actingAsStorefrontCustomer($otherCustomer)->postJson(
            route('order.product.validate-coupon', $product),
            [
                'quantity' => 1,
                'coupon_code' => 'VIPONLY',
            ]
        );

        $response->assertOk();
        $response->assertJson([
            'valid' => false,
            'reason' => 'not_for_account',
        ]);
        $response->assertJsonFragment([
            'message' => __('This coupon is not available for your account.'),
        ]);
    }

    public function test_logged_in_customer_can_apply_user_scoped_coupon(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-27 19:00:00', 'Asia/Kolkata'));

        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);
        $customer = $this->createStorefrontCustomer([
            'email' => 'lemicydi@mailinator.com',
            'name' => 'Fredericka Newton',
        ]);

        Coupon::factory()->fixed(199.99)->create([
            'code' => 'SPECIFICUSER',
            'from_date' => '2026-06-27',
            'to_date' => '2026-07-01',
            'user_scope' => Coupon::USER_SCOPE_USERS,
        ])->users()->attach($customer);

        $response = $this->actingAsStorefrontCustomer($customer)->postJson(
            route('order.product.validate-coupon', $product),
            [
                'quantity' => 1,
                'coupon_code' => 'SPECIFICUSER',
                'guest_email' => $customer->email,
            ]
        );

        $response->assertOk();
        $response->assertJson([
            'valid' => true,
            'discount_amount' => 199.99,
        ]);
    }

    public function test_coupon_starting_today_is_valid_in_site_timezone(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-27 19:00:00', 'Asia/Kolkata'));

        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);

        $coupon = Coupon::factory()->fixed(50)->create([
            'from_date' => '2026-06-27',
            'to_date' => '2026-07-01',
        ]);

        $response = $this->postJson(route('order.product.validate-coupon', $product), [
            'quantity' => 1,
            'coupon_code' => $coupon->code,
        ]);

        $response->assertOk();
        $response->assertJson([
            'valid' => true,
            'discount_amount' => 50,
        ]);
    }

    public function test_invalid_coupon_code_is_rejected(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);

        $this->verifyGuestEmail('buyer@example.com');

        $response = $this->post(route('order.store', $product), $this->orderPayload([
            'coupon_code' => 'NOTREAL',
        ]));

        $response->assertSessionHasErrors('coupon_code');
    }

    public function test_multiple_auto_apply_coupons_default_to_lowest_discount_on_order_place(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);

        $lowestDiscountCoupon = Coupon::factory()->autoApply()->fixed(50)->create([
            'code' => 'AUTO50',
            'label' => 'Fifty off',
        ]);
        Coupon::factory()->autoApply()->fixed(99)->create([
            'code' => 'AUTO99',
            'label' => 'Ninety nine off',
        ]);

        $response = $this->get(route('order.place', $product));

        $response->assertOk();
        $response->assertSee('Available offers', false);
        $html = preg_replace('/\s+/', ' ', $response->getContent());
        $this->assertMatchesRegularExpression(
            '/id="coupon_code"[^>]*value="AUTO50"/',
            $html,
        );

        $order = $this->placeOrder($product, ['coupon_code' => 'AUTO50']);

        $this->assertSame(50.0, (float) $order->discount_amount);
        $this->assertSame($lowestDiscountCoupon->id, $order->coupon_id);
        $this->assertSame('AUTO50', $order->coupon_code);
    }

    public function test_multiple_auto_apply_only_auto_applies_lowest_discount_on_submit(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);

        $lowestDiscountCoupon = Coupon::factory()->autoApply()->fixed(50)->create(['code' => 'AUTO50']);
        Coupon::factory()->autoApply()->fixed(99)->create(['code' => 'AUTO99']);

        $order = $this->placeOrder($product);

        $this->assertSame(50.0, (float) $order->discount_amount);
        $this->assertSame($lowestDiscountCoupon->id, $order->coupon_id);
        $this->assertSame('AUTO50', $order->coupon_code);
    }

    public function test_fixed_auto_apply_wins_over_percentage_when_discount_amount_is_lower(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 949]);

        $fixedCoupon = Coupon::factory()->autoApply()->fixed(50)->create([
            'code' => 'AUTO50',
            'label' => 'Auto 10% off',
        ]);
        Coupon::factory()->autoApply()->percentage(10)->create([
            'code' => 'AUTO10',
            'label' => 'Autoapply 10%',
        ]);

        $response = $this->get(route('order.place', $product));

        $response->assertOk();
        $html = preg_replace('/\s+/', ' ', $response->getContent());
        $this->assertMatchesRegularExpression(
            '/id="coupon_code"[^>]*value="AUTO50"/',
            $html,
        );

        $order = $this->placeOrder($product, ['coupon_code' => 'AUTO50']);

        $this->assertSame(50.0, (float) $order->discount_amount);
        $this->assertSame($fixedCoupon->id, $order->coupon_id);
    }

    public function test_validate_coupon_returns_lowest_discount_auto_apply_id_for_subtotal(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);

        $lowestDiscountCoupon = Coupon::factory()->autoApply()->fixed(50)->create(['code' => 'AUTO50']);
        Coupon::factory()->autoApply()->fixed(99)->create(['code' => 'AUTO99']);

        $response = $this->postJson(route('order.product.validate-coupon', $product), [
            'quantity' => 1,
            'auto_select_best' => true,
        ]);

        $response->assertOk();
        $response->assertJson([
            'valid' => true,
            'discount_amount' => 50,
            'best_coupon_id' => $lowestDiscountCoupon->id,
            'best_coupon_code' => 'AUTO50',
        ]);
    }

    public function test_validate_coupon_skip_auto_apply_returns_zero_discount(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);

        Coupon::factory()->autoApply()->fixed(50)->create(['code' => 'AUTO50']);

        $response = $this->postJson(route('order.product.validate-coupon', $product), [
            'quantity' => 1,
            'skip_auto_apply' => true,
        ]);

        $response->assertOk();
        $response->assertJson([
            'valid' => false,
            'discount_amount' => 0,
            'total' => 500,
        ]);
    }

    public function test_coupon_declined_prevents_auto_apply_on_submit(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);

        Coupon::factory()->autoApply()->fixed(50)->create(['code' => 'AUTO50']);

        $order = $this->placeOrder($product, [
            'coupon_code' => '',
            'coupon_declined' => true,
        ]);

        $this->assertSame(0.0, (float) $order->discount_amount);
        $this->assertNull($order->coupon_id);
        $this->assertNull($order->coupon_code);
    }

    public function test_selected_offer_code_applies_discount_on_submit(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);

        Coupon::factory()->autoApply()->fixed(50)->create(['code' => 'AUTO50']);
        Coupon::factory()->fixed(99)->create(['code' => 'ALL99']);

        $order = $this->placeOrder($product, ['coupon_code' => 'ALL99']);

        $this->assertSame(99.0, (float) $order->discount_amount);
        $this->assertSame('ALL99', $order->coupon_code);
    }

    public function test_single_auto_apply_coupon_prefills_code_input(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'price' => 500]);

        Coupon::factory()->autoApply()->fixed(50)->create(['code' => 'AUTO50']);

        $response = $this->get(route('order.place', $product));

        $response->assertOk();
        $response->assertDontSee('data-coupon-offers-list', false);
        $html = preg_replace('/\s+/', ' ', $response->getContent());
        $this->assertMatchesRegularExpression(
            '/id="coupon_code"[^>]*value="AUTO50"/',
            $html,
        );
    }

    public function test_order_confirm_shows_coupon_code_when_discount_applied(): void
    {
        Setting::set('theme', 'better-buns');
        Setting::flushCache();

        $product = Product::factory()->create(['status' => 'active', 'price' => 649]);

        Coupon::factory()->autoApply()->fixed(50)->create([
            'code' => 'AUTO50',
            'label' => 'Auto 10% off',
        ]);

        $order = $this->placeOrder($product);

        $response = $this->get(route('order.confirm', $order));

        $response->assertOk();
        $response->assertSee('Coupon code', false);
        $response->assertSee('AUTO50', false);
        $response->assertSee('649.00', false);
        $response->assertSee('599.00', false);
        $response->assertSee('50.00', false);
    }

    public function test_checkout_prefills_auto_apply_for_selected_variant_above_min_order(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-27 12:00:00', 'Asia/Kolkata'));

        $product = $this->createVariantProduct([
            500 => 799,
            1000 => 1499,
        ]);
        $variant1kg = $this->variantForWeight($product, 1000);

        Coupon::factory()->autoApply()->percentage(10, 500)->create([
            'code' => 'AUTO10',
            'from_date' => '2026-06-27',
            'to_date' => '2026-07-01',
            'min_order_amount' => 800,
        ]);

        $response = $this->get(route('order.place', [
            'product' => $product,
            'product_variant_id' => $variant1kg->id,
        ]));

        $response->assertOk();
        $html = preg_replace('/\s+/', ' ', $response->getContent());
        $this->assertMatchesRegularExpression(
            '/id="coupon_code"[^>]*value="AUTO10"/',
            $html,
        );
    }

    public function test_validate_coupon_returns_universal_coupons_for_qualifying_variant(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-27 12:00:00', 'Asia/Kolkata'));

        $product = $this->createVariantProduct([
            500 => 799,
            1000 => 1499,
        ]);
        $variant1kg = $this->variantForWeight($product, 1000);

        Coupon::factory()->autoApply()->percentage(10, 500)->create([
            'code' => 'AUTO10',
            'from_date' => '2026-06-27',
            'to_date' => '2026-07-01',
            'min_order_amount' => 800,
            'label' => 'Auto 10%',
        ]);
        Coupon::factory()->fixed(30)->create([
            'code' => 'SAVE30',
            'from_date' => '2026-06-27',
            'to_date' => '2026-07-01',
            'label' => 'Thirty off',
        ]);

        $response = $this->postJson(route('order.product.validate-coupon', $product), [
            'product_variant_id' => $variant1kg->id,
            'quantity' => 1,
            'auto_select_best' => true,
        ]);

        $response->assertOk();
        $response->assertJsonPath('best_coupon_code', 'AUTO10');
        $response->assertJsonCount(2, 'universal_coupons');
    }

    public function test_validate_coupon_excludes_min_order_coupon_for_low_variant_subtotal(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-27 12:00:00', 'Asia/Kolkata'));

        $product = $this->createVariantProduct([
            500 => 799,
            1000 => 1499,
        ]);
        $variant500g = $this->variantForWeight($product, 500);

        Coupon::factory()->autoApply()->percentage(10, 500)->create([
            'code' => 'AUTO10',
            'from_date' => '2026-06-27',
            'to_date' => '2026-07-01',
            'min_order_amount' => 800,
        ]);
        Coupon::factory()->fixed(30)->create([
            'code' => 'SAVE30',
            'from_date' => '2026-06-27',
            'to_date' => '2026-07-01',
        ]);

        $response = $this->postJson(route('order.product.validate-coupon', $product), [
            'product_variant_id' => $variant500g->id,
            'quantity' => 1,
            'skip_auto_apply' => true,
        ]);

        $response->assertOk();
        $response->assertJsonCount(1, 'universal_coupons');
        $response->assertJsonPath('universal_coupons.0.code', 'SAVE30');
    }
}
