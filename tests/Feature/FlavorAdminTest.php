<?php

namespace Tests\Feature;

use App\Models\Flavor;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlavorAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_admin_can_create_flavor(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->post(route('admin.flavors.store'), [
            'name_en' => 'Test Flavor',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $response->assertRedirect(route('admin.flavors.index'));
        $this->assertDatabaseHas('flavors', [
            'name_en' => 'Test Flavor',
            'slug' => 'test-flavor',
            'status' => 'active',
        ]);
    }

    public function test_user_without_permission_cannot_create_flavor(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson(route('admin.flavors.store'), [
            'name_en' => 'Blocked Flavor',
            'status' => 'active',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('flavors', ['name_en' => 'Blocked Flavor']);
    }

    public function test_admin_can_view_flavors_index(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');
        Flavor::factory()->create(['name_en' => 'Chocolate', 'slug' => 'chocolate']);

        $response = $this->actingAs($admin)->get(route('admin.flavors.index'));

        $response->assertOk()->assertSee('Chocolate');
    }
}
