<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\User\RegisteredVia;
use App\Services\CustomerAuthService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
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

    private function withVerifiedPhone(string $phone): CustomerAuthService
    {
        $service = app(CustomerAuthService::class);
        session([CustomerAuthService::SESSION_VERIFIED_PHONE => $phone]);

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

    public function test_phone_with_different_email_is_rejected(): void
    {
        $existing = User::factory()->customer()->create([
            'email' => 'stored@example.com',
            'phone' => '9222333444',
        ]);

        $this->expectException(ValidationException::class);

        try {
            $this->withVerifiedEmail('entered@example.com')
                ->resolveCustomerForVerifiedEmail('entered@example.com', '9222333444', 'Someone');
        } finally {
            $existing->refresh();
            $this->assertSame('stored@example.com', $existing->email);
        }
    }

    public function test_no_match_creates_new_customer(): void
    {
        $resolved = $this->withVerifiedEmail('brand@example.com')
            ->resolveCustomerForVerifiedEmail('brand@example.com', '9333444555', 'Brand New');

        $this->assertSame('brand@example.com', $resolved->email);
        $this->assertSame('9333444555', $resolved->phone);
        $this->assertTrue($resolved->isCustomer());
    }

    public function test_email_with_different_phone_is_rejected_when_verifying_phone(): void
    {
        $existing = User::factory()->customer()->create([
            'email' => 'owner@example.com',
            'phone' => '9444555666',
        ]);

        $this->expectException(ValidationException::class);

        try {
            $this->withVerifiedPhone('9777888999')
                ->resolveCustomerForVerifiedPhone('9777888999', 'Someone', 'owner@example.com');
        } finally {
            $existing->refresh();
            $this->assertSame('9444555666', $existing->phone);
        }
    }

    public function test_email_only_account_is_claimed_with_verified_phone(): void
    {
        $emailOnly = User::factory()->customer()->create([
            'name' => 'Store Guest',
            'email' => 'guest@example.com',
            'phone' => null,
        ]);

        $resolved = $this->withVerifiedPhone('9555666777')
            ->resolveCustomerForVerifiedPhone('9555666777', 'Store Guest', 'guest@example.com');

        $emailOnly->refresh();
        $this->assertSame($emailOnly->id, $resolved->id);
        $this->assertSame('9555666777', $emailOnly->phone);
    }

    public function test_no_match_creates_new_customer_for_verified_phone(): void
    {
        $resolved = $this->withVerifiedPhone('9666777888')
            ->resolveCustomerForVerifiedPhone('9666777888', 'Brand New', 'brand-phone@example.com');

        $this->assertSame('9666777888', $resolved->phone);
        $this->assertSame('brand-phone@example.com', $resolved->email);
        $this->assertTrue($resolved->isCustomer());
    }

    public function test_assert_contacts_not_conflicting_throws_for_different_accounts(): void
    {
        User::factory()->customer()->create([
            'email' => 'alice@example.com',
            'phone' => '9111000001',
        ]);
        User::factory()->customer()->create([
            'email' => 'bob@example.com',
            'phone' => '9111000002',
        ]);

        $this->expectException(ValidationException::class);

        app(CustomerAuthService::class)->assertContactsNotConflicting('alice@example.com', '9111000002');
    }

    public function test_assert_contacts_not_conflicting_allows_matching_account(): void
    {
        $existing = User::factory()->customer()->create([
            'email' => 'carol@example.com',
            'phone' => '9111000003',
        ]);

        app(CustomerAuthService::class)->assertContactsNotConflicting('carol@example.com', '9111000003');

        $this->assertTrue(true);
        $this->assertSame('carol@example.com', $existing->email);
    }

    public function test_assert_contacts_not_conflicting_allows_unknown_or_blank_values(): void
    {
        app(CustomerAuthService::class)->assertContactsNotConflicting('unknown@example.com', '9111000099');
        app(CustomerAuthService::class)->assertContactsNotConflicting(null, '9111000099');
        app(CustomerAuthService::class)->assertContactsNotConflicting('unknown@example.com', null);

        $this->assertTrue(true);
    }
}
