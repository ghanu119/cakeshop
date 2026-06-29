<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        return $admin;
    }

    public function test_guest_cannot_access_coupons_index(): void
    {
        $this->get(route('admin.coupons.index'))->assertRedirect();
    }

    public function test_admin_can_create_percentage_coupon(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.coupons.store'), [
            'code' => 'save10',
            'label' => 'Save 10%',
            'description' => 'Ten percent off',
            'from_date' => now()->toDateString(),
            'to_date' => now()->addMonth()->toDateString(),
            'discount_type' => 'percentage',
            'discount_amount' => 10,
            'max_discount_amount' => 200,
            'status' => 'active',
            'auto_apply' => 0,
            'product_scope' => 'all',
            'user_scope' => 'all',
        ]);

        $response->assertRedirect(route('admin.coupons.index'));
        $this->assertDatabaseHas('coupons', ['code' => 'SAVE10', 'label' => 'Save 10%']);
    }

    public function test_percentage_coupon_requires_max_discount(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.coupons.store'), [
            'code' => 'nopmax',
            'label' => 'No Max',
            'from_date' => now()->toDateString(),
            'to_date' => now()->addMonth()->toDateString(),
            'discount_type' => 'percentage',
            'discount_amount' => 10,
            'status' => 'active',
            'product_scope' => 'all',
            'user_scope' => 'all',
        ]);

        $response->assertSessionHasErrors('max_discount_amount');
    }

    public function test_auto_apply_coupon_clears_product_scope(): void
    {
        $admin = $this->adminUser();
        $product = Product::factory()->create();

        $this->actingAs($admin)->post(route('admin.coupons.store'), [
            'code' => 'AUTO',
            'label' => 'Auto',
            'from_date' => now()->toDateString(),
            'to_date' => now()->addMonth()->toDateString(),
            'discount_type' => 'fixed',
            'discount_amount' => 50,
            'status' => 'active',
            'auto_apply' => 1,
            'product_scope' => 'products',
            'product_ids' => [$product->id],
            'user_scope' => 'all',
        ]);

        $coupon = Coupon::where('code', 'AUTO')->first();
        $this->assertTrue($coupon->auto_apply);
        $this->assertSame('all', $coupon->product_scope);
        $this->assertCount(0, $coupon->products);
    }

    public function test_product_scoped_coupon_syncs_products(): void
    {
        $admin = $this->adminUser();
        $product = Product::factory()->create();

        $this->actingAs($admin)->post(route('admin.coupons.store'), [
            'code' => 'PROD1',
            'label' => 'Product coupon',
            'from_date' => now()->toDateString(),
            'to_date' => now()->addMonth()->toDateString(),
            'discount_type' => 'fixed',
            'discount_amount' => 25,
            'status' => 'active',
            'auto_apply' => 0,
            'product_scope' => 'products',
            'product_ids' => [$product->id],
            'user_scope' => 'all',
        ]);

        $coupon = Coupon::where('code', 'PROD1')->first();
        $this->assertTrue($coupon->products()->whereKey($product->id)->exists());
    }

    public function test_category_scoped_coupon_syncs_categories(): void
    {
        $admin = $this->adminUser();
        $category = Category::factory()->create();

        $this->actingAs($admin)->post(route('admin.coupons.store'), [
            'code' => 'CAT1',
            'label' => 'Category coupon',
            'from_date' => now()->toDateString(),
            'to_date' => now()->addMonth()->toDateString(),
            'discount_type' => 'fixed',
            'discount_amount' => 25,
            'status' => 'active',
            'auto_apply' => 0,
            'product_scope' => 'categories',
            'category_ids' => [$category->id],
            'user_scope' => 'all',
        ]);

        $coupon = Coupon::where('code', 'CAT1')->first();
        $this->assertTrue($coupon->categories()->whereKey($category->id)->exists());
    }
}
