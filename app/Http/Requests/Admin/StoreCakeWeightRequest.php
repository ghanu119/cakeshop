<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCakeWeightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', \App\Models\Setting::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'person_capacity_label' => ['nullable', 'string', 'max:255'],
            'grams' => ['required', 'integer', 'min:1'],
            'delivery_charge' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }

    public function attributes(): array
    {
        return [
            'label' => __('display label'),
            'grams' => __('weight in grams'),
        ];
    }
}
