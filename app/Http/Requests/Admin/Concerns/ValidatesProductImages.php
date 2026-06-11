<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Services\ProductImageService;
use Illuminate\Validation\Validator;

trait ValidatesProductImages
{
    protected function productImageRules(): array
    {
        return [
            'product_images' => ['nullable', 'array', 'max:'.ProductImageService::MAX_IMAGES],
            'product_images.*' => ['required', 'string', 'regex:'.ProductImageService::IMAGE_REF_PATTERN],
            'primary_image' => ['nullable', 'string', 'regex:'.ProductImageService::IMAGE_REF_PATTERN],
            'removed_media_ids' => ['nullable', 'array'],
            'removed_media_ids.*' => ['integer'],
        ];
    }

    protected function prepareProductImageInput(): void
    {
        if ($this->input('primary_image') === '') {
            $this->merge(['primary_image' => null]);
        }
    }

    /**
     * @return array<string, string>
     */
    protected function productImageMessages(): array
    {
        return [
            'product_images.*.regex' => __('One of the product images is invalid. Please remove it and upload again.'),
            'primary_image.regex' => __('The primary image selection is invalid. Please choose a primary image again.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function productImageAttributes(): array
    {
        return [
            'product_images.*' => __('product image'),
            'primary_image' => __('primary image'),
        ];
    }

    protected function validateProductImages(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $images = $this->input('product_images', []);
            $primary = $this->input('primary_image');

            if ($images !== [] && $primary && ! in_array($primary, $images, true)) {
                $validator->errors()->add('primary_image', __('The primary image must be one of the selected images.'));
            }
        });
    }
}
