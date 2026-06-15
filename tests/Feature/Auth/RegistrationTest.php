<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_route_redirects_to_account_login(): void
    {
        $this->get('/register')->assertRedirect('/account/login');
    }
}
