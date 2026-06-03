<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantOptionValue;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as IlluminateValidator;

trait ValidatesProductWeightVariants
{
    protected function prepareProductVariantInput(): void
    {
        $variants = $this->input('variants');

        if (! is_array($variants)) {
            $this->merge(['variants' => []]);

            return;
        }

        $normalized = [];

        foreach ($variants as $row) {
            if (! is_array($row)) {
                continue;
            }

            $valueId = (int) ($row['variant_option_value_id'] ?? 0);
            $priceRaw = $row['price'] ?? null;
            $hasPrice = $priceRaw !== null && $priceRaw !== '' && is_numeric($priceRaw);

            if ($valueId <= 0 && ! $hasPrice) {
                continue;
            }

            $normalized[] = [
                'id' => ! empty($row['id']) ? (int) $row['id'] : null,
                'variant_option_value_id' => $valueId,
                'price' => $hasPrice ? $priceRaw : $priceRaw,
            ];
        }

        $this->merge(['variants' => $normalized]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function productWeightVariantRules(): array
    {
        $variantCount = count($this->input('variants', []));

        return [
            'price' => [
                Rule::requiredIf($variantCount === 0),
                'nullable',
                'numeric',
                'min:0',
                'max:99999999.99',
            ],
            'variants' => ['nullable', 'array'],
            'variants.*.variant_option_value_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('variant_option_values', 'id')->where('status', 'active'),
            ],
            'variants.*.price' => [
                'required',
                'numeric',
                'min:0',
                'max:99999999.99',
            ],
            'variants.*.id' => [
                'nullable',
                'integer',
                Rule::exists('product_variants', 'id'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function productWeightVariantMessages(): array
    {
        return [
            'price.required' => __('Enter a product price, or add at least one weight with its price.'),
            'variants.*.variant_option_value_id.required' => __('Select a weight for each row.'),
            'variants.*.variant_option_value_id.distinct' => __('Each weight can only be added once.'),
            'variants.*.variant_option_value_id.exists' => __('The selected weight is invalid or inactive.'),
            'variants.*.price.required' => __('Enter a price for each weight.'),
            'variants.*.price.numeric' => __('Each weight price must be a valid number.'),
            'variants.*.price.min' => __('Each weight price must be zero or greater.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function productWeightVariantAttributes(): array
    {
        return [
            'variants.*.variant_option_value_id' => __('weight'),
            'variants.*.price' => __('weight price'),
            'price' => __('product price'),
        ];
    }

    protected function validateProductWeightVariants(IlluminateValidator $validator): void
    {
        $validator->after(function (IlluminateValidator $validator) {
            $variants = $this->input('variants', []);

            if ($variants === []) {
                return;
            }

            /** @var Product|null $product */
            $product = $this->route('product');

            foreach ($variants as $index => $row) {
                $valueId = (int) ($row['variant_option_value_id'] ?? 0);
                $price = $row['price'] ?? null;

                if ($valueId <= 0) {
                    $validator->errors()->add(
                        "variants.{$index}.variant_option_value_id",
                        __('Select a weight for each row.')
                    );
                } elseif (! VariantOptionValue::query()
                    ->where('id', $valueId)
                    ->where('status', 'active')
                    ->whereHas('type', fn ($q) => $q->where('slug', 'weight')->where('status', 'active'))
                    ->exists()) {
                    $validator->errors()->add(
                        "variants.{$index}.variant_option_value_id",
                        __('The selected weight is invalid or inactive.')
                    );
                }

                if ($price === null || $price === '' || ! is_numeric($price)) {
                    $validator->errors()->add(
                        "variants.{$index}.price",
                        __('Enter a price for each weight.')
                    );
                }

                if (! empty($row['id'])) {
                    if (! $product instanceof Product) {
                        $validator->errors()->add(
                            "variants.{$index}.id",
                            __('Invalid weight price row.')
                        );
                    } else {
                        $ownsVariant = ProductVariant::query()
                            ->where('id', (int) $row['id'])
                            ->where('product_id', $product->id)
                            ->exists();

                        if (! $ownsVariant) {
                            $validator->errors()->add(
                                "variants.{$index}.id",
                                __('Invalid weight price row for this product.')
                            );
                        }
                    }
                }
            }

            $valueIds = collect($variants)
                ->pluck('variant_option_value_id')
                ->filter(fn ($id) => (int) $id > 0)
                ->map(fn ($id) => (int) $id);

            if ($valueIds->count() !== $valueIds->unique()->count()) {
                $validator->errors()->add('variants', __('Each weight can only be added once.'));
            }
        });
    }
}
