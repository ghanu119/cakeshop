<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_redirects_customers_to_account_dashboard(): void
    {
        $user = $this->createStorefrontCustomer();

        $this->actingAs($user)
            ->get('/verify-email')
            ->assertRedirect(route('account.dashboard'));
    }
}
