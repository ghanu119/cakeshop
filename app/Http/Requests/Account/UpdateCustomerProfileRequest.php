<?php

namespace App\Http\Requests\Account;

use App\Models\User\UserGender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCustomer() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'birth_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'birth_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'anniversary_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'anniversary_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'gender' => ['nullable', 'string', Rule::in(array_keys(UserGender::options()))],
        ];
    }
}
