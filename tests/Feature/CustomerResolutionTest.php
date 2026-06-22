<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\User\RegisteredVia;
use App\Services\CustomerAuthService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerResolutionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    private function withVerifiedEmail(string $email): CustomerAuthService
    {
        $service = app(CustomerAuthService::class);
        session([CustomerAuthService::SESSION_VERIFIED_EMAIL => strtolower(trim($email))]);

        return $service;
    }

    public function test_email_match_wins_over_phone_only_duplicate(): void
    {
        $emailCustomer = User::factory()->customer()->create([
            'email' => 'email@example.com',
            'phone' => '9000000001',
        ]);
        User::factory()->customer()->create([
            'email' => null,
            'phone' => '9000000002',
            'registered_via' => RegisteredVia::ADMIN_CREATED,
        ]);

        $resolved = $this->withVerifiedEmail('email@example.com')
            ->resolveCustomerForVerifiedEmail('email@example.com', '9000000002', 'Email User');

        $this->assertSame($emailCustomer->id, $resolved->id);
    }

    public function test_phone_only_record_is_claimed_with_entered_email(): void
    {
        $phoneOnly = User::factory()->customer()->create([
            'name' => 'Store Guest',
            'email' => null,
            'phone' => '9111222333',
            'registered_via' => RegisteredVia::ADMIN_CREATED,
        ]);

        $resolved = $this->withVerifiedEmail('new@example.com')
            ->resolveCustomerForVerifiedEmail('new@example.com', '9111222333', 'Store Guest');

        $phoneOnly->refresh();
        $this->assertSame($phoneOnly->id, $resolved->id);
        $this->assertSame('new@example.com', $phoneOnly->email);
    }

    public function test_phone_with_different_email_auths_without_changing_stored_email(): void
    {
        $existing = User::factory()->customer()->create([
            'email' => 'stored@example.com',
            'phone' => '9222333444',
        ]);

        $resolved = $this->withVerifiedEmail('entered@example.com')
            ->resolveCustomerForVerifiedEmail('entered@example.com', '9222333444', 'Someone');

        $existing->refresh();
        $this->assertSame($existing->id, $resolved->id);
        $this->assertSame('stored@example.com', $existing->email);
    }

    public function test_no_match_creates_new_customer(): void
    {
        $resolved = $this->withVerifiedEmail('brand@example.com')
            ->resolveCustomerForVerifiedEmail('brand@example.com', '9333444555', 'Brand New');

        $this->assertSame('brand@example.com', $resolved->email);
        $this->assertSame('9333444555', $resolved->phone);
        $this->assertTrue($resolved->isCustomer());
    }
}
