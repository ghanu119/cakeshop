<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductListTest extends TestCase
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

    public function test_admin_can_sort_products_by_price_descending(): void
    {
        $admin = $this->adminUser();
        $category = Category::factory()->create();

        Product::factory()->create([
            'category_id' => $category->id,
            'name_en' => 'Alpha Cake',
            'price' => 100,
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'name_en' => 'Zulu Cake',
            'price' => 500,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.products.index', [
            'sort' => 'price',
            'direction' => 'desc',
        ]));

        $response->assertOk();
        $response->assertSeeInOrder(['Zulu Cake', 'Alpha Cake']);
    }

    public function test_admin_product_list_shows_last_modified_column(): void
    {
        $admin = $this->adminUser();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'updated_at' => now()->setTimezone('Asia/Kolkata')->setDate(2026, 6, 1)->setTime(14, 30),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.products.index'));

        $response->assertOk();
        $response->assertSee(__('Last modified'));
        $response->assertSee($product->updated_at->setTimezone('Asia/Kolkata')->format('d M Y H:i'));
    }

    public function test_admin_product_list_paginates_results(): void
    {
        $admin = $this->adminUser();
        $category = Category::factory()->create();

        Product::factory()->count(16)->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin)->get(route('admin.products.index'));

        $response->assertOk();
        $response->assertSee('page=2', false);
    }
}
