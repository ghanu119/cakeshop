<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_redirects_to_account_login(): void
    {
        $this->get('/login')->assertRedirect('/account/login');
    }

    public function test_register_redirects_to_account_login(): void
    {
        $this->get('/register')->assertRedirect('/account/login');
    }
}
