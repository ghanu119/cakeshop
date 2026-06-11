<?php

namespace Tests\Feature;

use App\Models\Flavor;
use App\Models\User;
use App\Services\FlavorService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlavorSlugSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_soft_deleted_flavor_releases_slug_on_delete(): void
    {
        $flavor = Flavor::factory()->create([
            'name_en' => 'Dark Chocolate',
            'slug' => 'dark-chocolate',
        ]);

        $flavor->delete();

        $flavor->refresh();
        $this->assertSoftDeleted($flavor);
        $this->assertSame('dark-chocolate-deleted-'.$flavor->id, $flavor->slug);
    }

    public function test_admin_can_create_flavor_with_same_name_after_soft_delete(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $deleted = Flavor::factory()->create([
            'name_en' => 'Dark Chocolate',
            'slug' => 'dark-chocolate',
        ]);
        $deleted->delete();

        $response = $this->actingAs($admin)->post(route('admin.flavors.store'), [
            'name_en' => 'Dark Chocolate',
            'status' => 'active',
            'sort_order' => 0,
        ]);

        $response->assertRedirect(route('admin.flavors.index'));
        $this->assertDatabaseHas('flavors', [
            'name_en' => 'Dark Chocolate',
            'slug' => 'dark-chocolate',
            'deleted_at' => null,
        ]);
    }

    public function test_service_reclaims_slug_from_existing_soft_deleted_flavor(): void
    {
        $deleted = Flavor::factory()->create([
            'name_en' => 'Dark Chocolate',
            'slug' => 'dark-chocolate',
        ]);
        $deleted->delete();
        $deleted->update(['slug' => 'dark-chocolate']);

        $flavor = app(FlavorService::class)->createOrUpdate(null, [
            'name_en' => 'Dark Chocolate',
            'status' => 'active',
            'sort_order' => 0,
        ]);

        $this->assertSame('dark-chocolate', $flavor->slug);
        $deleted->refresh();
        $this->assertSame('dark-chocolate-deleted-'.$deleted->id, $deleted->slug);
    }
}
