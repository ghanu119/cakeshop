<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_route_redirects_to_auth_modal(): void
    {
        $this->get('/register')->assertRedirect('/?auth=1');
    }
}
