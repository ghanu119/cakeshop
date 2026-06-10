<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureAdminHttpsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_http_admin_login_redirects_to_https_on_test_domain(): void
    {
        $this->app->detectEnvironment(fn () => 'local');

        $response = $this->get('http://cakeshop.test/admin/login');

        $response->assertRedirect('https://cakeshop.test/admin/login');
    }

    public function test_https_admin_dashboard_is_reachable_for_staff(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->get('https://cakeshop.test/admin/dashboard');

        $response->assertOk();
    }
}
