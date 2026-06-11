<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Services\CategoryService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorySlugSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_soft_deleted_category_releases_slug_on_delete(): void
    {
        $category = Category::factory()->create([
            'name_en' => 'Birthday Cakes',
            'slug' => 'birthday-cakes',
        ]);

        $category->delete();

        $category->refresh();
        $this->assertSoftDeleted($category);
        $this->assertSame('birthday-cakes-deleted-'.$category->id, $category->slug);
    }

    public function test_admin_can_create_category_with_same_name_after_soft_delete(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $deleted = Category::factory()->create([
            'name_en' => 'Birthday Cakes',
            'slug' => 'birthday-cakes',
        ]);
        $deleted->delete();

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name_en' => 'Birthday Cakes',
            'status' => 'active',
            'sort_order' => 0,
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name_en' => 'Birthday Cakes',
            'slug' => 'birthday-cakes',
            'deleted_at' => null,
        ]);
    }

    public function test_service_reclaims_slug_from_existing_soft_deleted_category(): void
    {
        $deleted = Category::factory()->create([
            'name_en' => 'Birthday Cakes',
            'slug' => 'birthday-cakes',
        ]);
        $deleted->delete();
        $deleted->update(['slug' => 'birthday-cakes']);

        $category = app(CategoryService::class)->createOrUpdate(null, [
            'name_en' => 'Birthday Cakes',
            'status' => 'active',
            'sort_order' => 0,
        ]);

        $this->assertSame('birthday-cakes', $category->slug);
        $deleted->refresh();
        $this->assertSame('birthday-cakes-deleted-'.$deleted->id, $deleted->slug);
    }
}
