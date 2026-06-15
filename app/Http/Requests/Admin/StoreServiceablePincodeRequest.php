<?php

namespace App\Http\Requests\Admin;

use App\Models\ServiceablePincode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceablePincodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ServiceablePincode::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'pincode' => ['required', 'string', 'digits:6', Rule::unique('serviceable_pincodes', 'pincode')],
            'locality' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
