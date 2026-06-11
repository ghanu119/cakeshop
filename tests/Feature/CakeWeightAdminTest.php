<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\VariantOptionValue;
use App\Services\ProductVariantService;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\VariantOptionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CakeWeightAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(VariantOptionSeeder::class);
    }

    public function test_admin_can_delete_unused_weight(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $weight = VariantOptionValue::query()
            ->forTypeSlug('weight')
            ->where('grams', 3000)
            ->firstOrFail();

        $response = $this->actingAs($admin)->delete(route('admin.cake-weights.destroy', $weight));

        $response->assertRedirect(route('admin.cake-weights.index'));
        $response->assertSessionHas('status', __('Weight option removed.'));
        $this->assertSoftDeleted('variant_option_values', ['id' => $weight->id]);
    }

    public function test_cannot_delete_weight_used_on_product(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $product = Product::factory()->create();
        $weight500 = VariantOptionValue::query()->forTypeSlug('weight')->where('grams', 500)->firstOrFail();

        app(ProductVariantService::class)->syncVariants($product, [
            ['variant_option_value_id' => $weight500->id, 'price' => 500],
        ]);

        $response = $this->actingAs($admin)->from(route('admin.cake-weights.index'))
            ->delete(route('admin.cake-weights.destroy', $weight500));

        $response->assertRedirect(route('admin.cake-weights.index'));
        $response->assertSessionHas('error', __('This weight is used on products. Set it to inactive instead of deleting.'));
        $this->assertNotSoftDeleted('variant_option_values', ['id' => $weight500->id]);
    }

    public function test_user_without_permission_cannot_delete_weight(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $weight = VariantOptionValue::query()
            ->forTypeSlug('weight')
            ->where('grams', 3000)
            ->firstOrFail();

        $response = $this->actingAs($user)->delete(route('admin.cake-weights.destroy', $weight));

        $response->assertForbidden();
        $this->assertNotSoftDeleted('variant_option_values', ['id' => $weight->id]);
    }
}
