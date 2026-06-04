<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFlavorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('flavors.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'name_en' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'badge_color' => ['nullable', 'string', 'max:32'],
        ];
    }
}
