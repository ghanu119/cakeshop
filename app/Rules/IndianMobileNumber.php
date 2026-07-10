<?php

namespace App\Rules;

use App\Support\PhoneNormalizer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IndianMobileNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! PhoneNormalizer::isValidIndianMobile(is_string($value) ? $value : null)) {
            $fail(__('Please enter a valid 10-digit mobile number.'));
        }
    }
}
