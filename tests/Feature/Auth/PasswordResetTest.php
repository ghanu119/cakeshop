<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_redirects_to_admin_login(): void
    {
        $this->get('/forgot-password')->assertRedirect('/admin/login');
    }

    public function test_reset_password_redirects_to_admin_login(): void
    {
        $this->get('/reset-password/some-token')->assertRedirect('/admin/login');
    }
}
