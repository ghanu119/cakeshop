<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesProductImages;
use App\Http\Requests\Admin\Concerns\ValidatesProductWeightVariants;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    use ValidatesProductImages;
    use ValidatesProductWeightVariants;

    public function authorize(): bool
    {
        return $this->user()?->can('products.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareProductVariantInput();
        $this->prepareProductImageInput();
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
            'message_on_cake_max_length' => ['nullable', 'integer', 'min:'.Order::MESSAGE_ON_CAKE_MIN_LENGTH, 'max:'.Order::MESSAGE_ON_CAKE_LIMIT_MAX],
            'sku' => ['nullable', 'string', 'max:64', Rule::unique('products', 'sku')],
            'earliest_delivery_label' => ['nullable', 'string', 'max:100'],
            'min_hours_before_delivery' => ['nullable', 'integer', 'min:1', 'max:720'],
            'show_whatsapp_customize_help' => ['nullable', 'boolean'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'show_on_homepage' => ['nullable', 'boolean'],
            'is_highlight' => ['nullable', 'boolean'],
            'is_trending' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'homepage_sort_order' => ['nullable', 'integer', 'min:0'],
            'flavor_ids' => ['nullable', 'array'],
            'flavor_ids.*' => ['integer', 'exists:flavors,id'],
            'delivery_charge' => [
                'nullable',
                'numeric',
                'min:0',
                Rule::prohibitedIf(fn () => ! empty($this->input('variants'))),
            ],
        ], $this->productWeightVariantRules(), $this->productImageRules());
    }

    public function messages(): array
    {
        return array_merge(
            $this->productWeightVariantMessages(),
            $this->productImageMessages(),
        );
    }

    public function attributes(): array
    {
        return array_merge(
            $this->productWeightVariantAttributes(),
            $this->productImageAttributes(),
        );
    }

    public function withValidator($validator): void
    {
        $this->validateProductWeightVariants($validator);
        $this->validateProductImages($validator);
    }
}
