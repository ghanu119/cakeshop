<?php

namespace App\Http\Requests\Account;

use App\Support\PhoneNormalizer;

class RegisterCustomerRequest extends AccountFormRequest
{
    public function authorize(): bool
    {
        return $this->session()->has('customer_otp_verified_email');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('phone')) {
            $this->merge([
                'phone' => PhoneNormalizer::normalize($this->input('phone')) ?? $this->input('phone'),
            ]);
        }
    }

    protected function accountValidationRedirectUrl(): string
    {
        return route('account.register');
    }
}
