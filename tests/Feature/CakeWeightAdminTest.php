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
        $response->assertSessionHasErrors('_form');
        $this->assertNotSoftDeleted('variant_option_values', ['id' => $weight500->id]);
    }

    public function test_user_without_permission_cannot_delete_weight(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $weight = VariantOptionValue::query()
            ->forTypeSlug('weight')
            ->where('grams', 3000)
            ->firstOrFail();

        $response = $this->actingAs($user)->deleteJson(route('admin.cake-weights.destroy', $weight));

        $response->assertForbidden();
        $this->assertNotSoftDeleted('variant_option_values', ['id' => $weight->id]);
    }

    public function test_admin_can_create_weight_with_person_capacity_label(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->post(route('admin.cake-weights.store'), [
            'label' => '4 KG',
            'person_capacity_label' => '30 - 35 People',
            'grams' => 4000,
            'sort_order' => 6,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.cake-weights.index'));
        $this->assertDatabaseHas('variant_option_values', [
            'grams' => 4000,
            'label' => '4 KG',
            'person_capacity_label' => '30 - 35 People',
        ]);
    }

    public function test_admin_can_update_person_capacity_label(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $weight = VariantOptionValue::query()
            ->forTypeSlug('weight')
            ->where('grams', 500)
            ->firstOrFail();

        $response = $this->actingAs($admin)->put(route('admin.cake-weights.update', $weight), [
            'label' => $weight->label,
            'person_capacity_label' => '5 - 6 People',
            'grams' => $weight->grams,
            'sort_order' => $weight->sort_order,
            'status' => $weight->status,
        ]);

        $response->assertRedirect(route('admin.cake-weights.index'));
        $this->assertDatabaseHas('variant_option_values', [
            'id' => $weight->id,
            'person_capacity_label' => '5 - 6 People',
        ]);
    }

    public function test_admin_can_set_delivery_charge_on_weight(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $weight = VariantOptionValue::query()
            ->forTypeSlug('weight')
            ->where('grams', 1000)
            ->firstOrFail();

        $response = $this->actingAs($admin)->put(route('admin.cake-weights.update', $weight), [
            'label' => $weight->label,
            'grams' => $weight->grams,
            'delivery_charge' => 45,
            'sort_order' => $weight->sort_order,
            'status' => $weight->status,
        ]);

        $response->assertRedirect(route('admin.cake-weights.index'));
        $this->assertDatabaseHas('variant_option_values', [
            'id' => $weight->id,
            'delivery_charge' => 45.00,
        ]);
    }

    public function test_delivery_charge_is_optional_and_defaults_to_null(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->post(route('admin.cake-weights.store'), [
            'label' => '5 KG',
            'grams' => 5000,
            'sort_order' => 7,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.cake-weights.index'));
        $this->assertDatabaseHas('variant_option_values', [
            'grams' => 5000,
            'delivery_charge' => null,
        ]);
    }
}
