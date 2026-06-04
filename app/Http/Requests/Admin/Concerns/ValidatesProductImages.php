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
