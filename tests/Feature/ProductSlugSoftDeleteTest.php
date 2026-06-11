<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSlugSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_soft_deleted_product_releases_slug_on_delete(): void
    {
        $product = Product::factory()->create([
            'name_en' => 'Chocolate Truffle Cake',
            'slug' => 'chocolate-truffle-cake',
        ]);

        $product->delete();

        $product->refresh();
        $this->assertSoftDeleted($product);
        $this->assertSame('chocolate-truffle-cake-deleted-'.$product->id, $product->slug);
    }

    public function test_admin_can_create_product_with_same_name_after_soft_delete(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $category = Category::factory()->create();
        $deleted = Product::factory()->create([
            'category_id' => $category->id,
            'name_en' => 'Chocolate Truffle Cake',
            'slug' => 'chocolate-truffle-cake',
        ]);
        $deleted->delete();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name_en' => 'Chocolate Truffle Cake',
            'price' => 413,
            'status' => 'inactive',
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', [
            'name_en' => 'Chocolate Truffle Cake',
            'slug' => 'chocolate-truffle-cake',
            'deleted_at' => null,
        ]);
    }

    public function test_service_reclaims_slug_from_existing_soft_deleted_product(): void
    {
        $category = Category::factory()->create();
        $deleted = Product::factory()->create([
            'category_id' => $category->id,
            'name_en' => 'Chocolate Truffle Cake',
            'slug' => 'chocolate-truffle-cake',
        ]);
        $deleted->delete();
        $deleted->update(['slug' => 'chocolate-truffle-cake']);

        $product = app(\App\Services\ProductService::class)->createOrUpdate(null, [
            'category_id' => $category->id,
            'name_en' => 'Chocolate Truffle Cake',
            'price' => 413,
            'status' => 'active',
        ]);

        $this->assertSame('chocolate-truffle-cake', $product->slug);
        $deleted->refresh();
        $this->assertSame('chocolate-truffle-cake-deleted-'.$deleted->id, $deleted->slug);
    }
}
