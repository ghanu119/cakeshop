<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_logged_in_customer_can_open_admin_login_page(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('Customer');

        $this->actingAs($customer)
            ->get(route('admin.login'))
            ->assertOk()
            ->assertSee(__('Admin Login'), false)
            ->assertSee(__('You are signed in as customer'), false);
    }

    public function test_logged_in_staff_is_redirected_from_admin_login_page(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $this->actingAs($admin)
            ->get(route('admin.login'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_logged_in_customer_can_switch_to_admin_login(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('Customer');

        $admin = User::factory()->create([
            'email' => 'staff@example.com',
            'password' => Hash::make('Str0n9@123'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('Admin');

        $this->actingAs($customer)
            ->post(route('admin.login.post'), [
                'email' => 'staff@example.com',
                'password' => 'Str0n9@123',
            ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertFalse(auth()->user()->hasRole('Customer'));
    }
}
