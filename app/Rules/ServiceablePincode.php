<?php

namespace App\Rules;

use App\Services\ServiceablePincodeService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ServiceablePincode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $service = app(ServiceablePincodeService::class);

        if (! $service->isServiceable((string) $value)) {
            $fail(__('Sorry, we do not deliver to this pincode yet. Please choose Take away or contact us.'));
        }
    }
}
