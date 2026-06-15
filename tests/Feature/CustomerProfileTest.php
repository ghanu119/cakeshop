<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\User\UserGender;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_customer_can_update_profile_marketing_fields(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->put(route('account.profile.update'), [
                'name' => 'Updated Name',
                'birth_day' => 15,
                'birth_month' => 6,
                'anniversary_day' => 20,
                'anniversary_month' => 12,
                'gender' => UserGender::FEMALE,
            ])
            ->assertRedirect(route('account.profile.edit'));

        $customer->refresh();
        $this->assertSame('Updated Name', $customer->name);
        $this->assertSame(15, $customer->birth_day);
        $this->assertSame(UserGender::FEMALE, $customer->gender);
    }
}
