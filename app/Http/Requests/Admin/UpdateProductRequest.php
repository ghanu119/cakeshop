<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesProductWeightVariants;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    use ValidatesProductWeightVariants;

    public function authorize(): bool
    {
        return $this->user()?->can('products.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareProductVariantInput();
    }

    public function rules(): array
    {
        return array_merge([
            'category_id' => ['required', 'exists:categories,id'],
            'name_en' => ['required', 'string', 'max:255'],
            'name_hi' => ['nullable', 'string', 'max:255'],
            'name_gu' => ['nullable', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_hi' => ['nullable', 'string'],
            'description_gu' => ['nullable', 'string'],
            'ingredients' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'show_on_homepage' => ['nullable', 'boolean'],
            'is_highlight' => ['nullable', 'boolean'],
            'is_trending' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'homepage_sort_order' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:5120'],
            'flavor_ids' => ['nullable', 'array'],
            'flavor_ids.*' => ['integer', 'exists:flavors,id'],
        ], $this->productWeightVariantRules());
    }

    public function messages(): array
    {
        return $this->productWeightVariantMessages();
    }

    public function attributes(): array
    {
        return $this->productWeightVariantAttributes();
    }

    public function withValidator($validator): void
    {
        $this->validateProductWeightVariants($validator);
    }
}
