<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Flavor;
use App\Models\VariantOptionValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', Rule::exists(Category::class, 'id')],
            'flavor_ids' => ['nullable', 'array'],
            'flavor_ids.*' => ['integer', Rule::exists(Flavor::class, 'id')->where('status', 'active')],
            'weight_ids' => ['nullable', 'array'],
            'weight_ids.*' => [
                'integer',
                Rule::exists(VariantOptionValue::class, 'id')->where('status', 'active'),
            ],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['nullable', 'string', Rule::in(['name_asc', 'name_desc', 'price_asc', 'price_desc', 'newest'])],
        ];
    }
}
