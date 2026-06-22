<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_redirects_to_storefront_auth(): void
    {
        $this->get('/forgot-password')->assertRedirect('/?auth=1');
    }

    public function test_reset_password_redirects_to_storefront_auth(): void
    {
        $this->get('/reset-password/some-token')->assertRedirect('/?auth=1');
    }
}
