<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCustomerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_admin_can_create_phone_only_customer(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $this->actingAs($admin)
            ->post(route('admin.customers.store'), [
                'name' => 'Walk In Guest',
                'phone' => '9887766554',
                'email' => '',
            ])
            ->assertRedirect();

        $customer = User::customers()->where('phone', '9887766554')->first();
        $this->assertNotNull($customer);
        $this->assertNull($customer->email);
    }

    public function test_lookup_finds_existing_customer_by_phone(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $customer = User::factory()->customer()->create([
            'email' => null,
            'phone' => '9776655443',
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.customers.lookup', ['phone' => '9776655443']));

        $response->assertOk()
            ->assertJsonPath('match.id', $customer->id);
    }

    public function test_duplicate_create_is_blocked_when_customer_exists(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        User::factory()->customer()->create([
            'name' => 'John Local',
            'email' => 'johnlocal@mailinator.com',
            'phone' => '9665544332',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.customers.create'))
            ->post(route('admin.customers.store'), [
                'name' => 'Duplicate',
                'phone' => '9665544332',
                'email' => 'dup@example.com',
            ])
            ->assertRedirect(route('admin.customers.create'))
            ->assertSessionHasErrors('phone');

        $this->actingAs($admin)
            ->get(route('admin.customers.create'))
            ->assertOk()
            ->assertSee(__('Matching customer found'), false)
            ->assertSee('John Local', false)
            ->assertSee('johnlocal@mailinator.com', false)
            ->assertSee(__('View profile'), false)
            ->assertSee(__('Shop as customer'), false)
            ->assertSee(route('admin.customers.impersonate', User::customers()->where('phone', '9665544332')->first()), false);
    }

    public function test_shop_as_customer_from_create_page_starts_impersonation(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $customer = User::factory()->customer()->create([
            'name' => 'John Local',
            'email' => 'johnlocal@mailinator.com',
            'phone' => '9665544332',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.customers.impersonate', $customer))
            ->assertRedirect(route('products.index'));

        $this->assertTrue(app(\App\Services\CustomerContext::class)->isImpersonating());
    }
}
