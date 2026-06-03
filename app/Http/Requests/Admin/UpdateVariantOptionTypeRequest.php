<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVariantOptionTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    public function rules(): array
    {
        $type = $this->route('variant_option_type');

        return [
            'slug' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('variant_option_types', 'slug')->ignore($type?->id)],
            'name_en' => ['required', 'string', 'max:255'],
            'selection_mode' => ['required', 'string', 'in:single,multiple'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }
}
