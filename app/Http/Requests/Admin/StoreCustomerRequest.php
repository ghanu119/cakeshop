<?php

namespace App\Http\Requests\Admin;

use App\Support\PhoneNormalizer;
use App\Support\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\User::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50', ValidationRules::uniqueAmongActive('users', 'phone')],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', ValidationRules::uniqueAmongActive('users', 'email')],
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
}
