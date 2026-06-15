<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Concerns\CreatesStorefrontCustomers;

abstract class TestCase extends BaseTestCase
{
    use CreatesStorefrontCustomers;
}
