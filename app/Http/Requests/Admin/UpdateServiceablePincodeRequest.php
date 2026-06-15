<?php

namespace App\Http\Requests\Admin;

use App\Models\ServiceablePincode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceablePincodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pincode = $this->route('serviceable_pincode');

        return $pincode && ($this->user()?->can('update', $pincode) ?? false);
    }

    public function rules(): array
    {
        /** @var ServiceablePincode $pincode */
        $pincode = $this->route('serviceable_pincode');

        return [
            'pincode' => ['required', 'string', 'digits:6', Rule::unique('serviceable_pincodes', 'pincode')->ignore($pincode->id)],
            'locality' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
