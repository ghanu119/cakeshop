<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Flavor;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductFlavorSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_product_update_syncs_flavor_pivot(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $flavorA = Flavor::factory()->create(['slug' => 'flavor-a', 'name_en' => 'Flavor A']);
        $flavorB = Flavor::factory()->create(['slug' => 'flavor-b', 'name_en' => 'Flavor B']);

        $response = $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'category_id' => $category->id,
            'name_en' => $product->name_en,
            'price' => $product->price,
            'status' => 'active',
            'flavor_ids' => [$flavorA->id, $flavorB->id],
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $product->refresh();
        $this->assertEquals([$flavorA->id, $flavorB->id], $product->flavors()->pluck('flavors.id')->all());
    }

    public function test_product_service_sync_flavors_preserves_sort_order(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $first = Flavor::factory()->create(['slug' => 'first']);
        $second = Flavor::factory()->create(['slug' => 'second']);

        app(ProductService::class)->syncFlavors($product, [$second->id, $first->id]);

        $this->assertEquals(
            [$second->id, $first->id],
            $product->flavors()->pluck('flavors.id')->all()
        );
        $this->assertSame(0, $product->flavors()->where('flavors.id', $second->id)->first()->pivot->sort_order);
        $this->assertSame(1, $product->flavors()->where('flavors.id', $first->id)->first()->pivot->sort_order);
    }
}
