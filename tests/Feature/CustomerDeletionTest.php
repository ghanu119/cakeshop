<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\CustomerDeletionService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_customer_can_delete_own_account(): void
    {
        $customer = User::factory()->customer()->create([
            'email' => 'delete-me@example.com',
            'phone' => '9000000099',
        ]);

        $this->actingAs($customer)
            ->delete(route('account.profile.destroy'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
        $trashed = User::withTrashed()->find($customer->id);
        $this->assertNotNull($trashed->deleted_at);
        $this->assertStringContainsString('-deleted-', $trashed->email);
    }

    public function test_deleted_customer_orders_not_visible_to_new_account(): void
    {
        $customer = User::factory()->customer()->create([
            'email' => 'gone@example.com',
            'phone' => '9000000088',
        ]);
        $order = Order::factory()->create(['user_id' => $customer->id, 'guest_email' => 'gone@example.com']);

        $this->actingAs($customer)
            ->delete(route('account.profile.destroy'));

        $newCustomer = User::factory()->customer()->create([
            'email' => 'gone@example.com',
            'phone' => '9000000088',
        ]);

        $this->actingAs($newCustomer)
            ->get(route('account.orders.index'))
            ->assertOk()
            ->assertDontSee($order->order_no);
    }
}
