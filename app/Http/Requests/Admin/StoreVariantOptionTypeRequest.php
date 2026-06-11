<?php

namespace App\Http\Requests\Admin;

use App\Support\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreVariantOptionTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:50', 'alpha_dash', ValidationRules::uniqueAmongActive('variant_option_types', 'slug')],
            'name_en' => ['required', 'string', 'max:255'],
            'selection_mode' => ['required', 'string', 'in:single,multiple'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }
}
