<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\CustomerDeletionService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPurgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        config(['privacy.customer_retention_days' => 90]);
    }

    public function test_purge_command_hard_deletes_expired_customers(): void
    {
        $customer = User::factory()->customer()->create();
        $order = Order::factory()->create(['user_id' => $customer->id]);

        $customer->delete();
        $customer->forceFill(['deleted_at' => now()->subDays(91)])->saveQuietly();

        $this->artisan('customers:purge-expired')->assertSuccessful();

        $this->assertDatabaseMissing('users', ['id' => $customer->id]);
        $order->refresh();
        $this->assertNull($order->user_id);
    }

    public function test_staff_users_are_not_purged(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $admin->delete();
        $admin->forceFill(['deleted_at' => now()->subDays(120)])->saveQuietly();

        $this->artisan('customers:purge-expired')->assertSuccessful();

        $this->assertNotNull(User::withTrashed()->find($admin->id));
    }
}
