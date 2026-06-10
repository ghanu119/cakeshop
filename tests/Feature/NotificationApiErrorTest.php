<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewOrderAdminNotification;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationApiErrorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_unauthenticated_notification_api_returns_json_error(): void
    {
        $response = $this->getJson(route('admin.notifications.index'));

        $response->assertUnauthorized()
            ->assertJsonStructure(['success', 'message']);
    }

    public function test_mark_read_returns_json_not_found_for_foreign_notification(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)
            ->postJson(route('admin.notifications.read', '00000000-0000-0000-0000-000000000099'));

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_notification_list_returns_json_envelope(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');
        $order = Order::factory()->for(Product::factory()->create())->create();
        $admin->notify(new NewOrderAdminNotification($order));

        $response = $this->actingAs($admin)
            ->getJson(route('admin.notifications.index'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.items.0.title', 'New order received');
    }
}
