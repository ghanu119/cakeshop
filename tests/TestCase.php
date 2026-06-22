<?php

namespace Tests;

use App\Models\User;
use App\Support\AuthGuards;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Concerns\CreatesStorefrontCustomers;

abstract class TestCase extends BaseTestCase
{
    use CreatesStorefrontCustomers;

    public function actingAs($user, $guard = null): static
    {
        if ($guard === null && $user instanceof User && $user->isCustomer() && ! $user->isStaff()) {
            $guard = AuthGuards::CUSTOMER;
        }

        return parent::actingAs($user, $guard);
    }
}
