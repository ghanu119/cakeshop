<?php

namespace Tests\Unit;

use App\Support\PhoneNormalizer;
use Tests\TestCase;

class PhoneNormalizerTest extends TestCase
{
    public function test_is_valid_indian_mobile_accepts_ten_digit_number(): void
    {
        $this->assertTrue(PhoneNormalizer::isValidIndianMobile('9876543210'));
        $this->assertTrue(PhoneNormalizer::isValidIndianMobile('+91 9876543210'));
    }

    public function test_is_valid_indian_mobile_rejects_short_or_invalid_numbers(): void
    {
        $this->assertFalse(PhoneNormalizer::isValidIndianMobile('12345'));
        $this->assertFalse(PhoneNormalizer::isValidIndianMobile('5876543210'));
        $this->assertFalse(PhoneNormalizer::isValidIndianMobile(''));
    }
}
