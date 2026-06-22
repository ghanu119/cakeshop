<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_redirects_to_auth_modal(): void
    {
        $this->get('/login')->assertRedirect('/?auth=1');
    }

    public function test_register_redirects_to_auth_modal(): void
    {
        $this->get('/register')->assertRedirect('/?auth=1');
    }
}
