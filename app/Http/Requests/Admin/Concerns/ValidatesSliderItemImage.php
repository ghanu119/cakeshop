<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Models\SliderItem;
use App\Services\SliderItemService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesSliderItemImage
{
    protected function sliderItemImageRules(bool $isUpdate = false): array
    {
        $tempPattern = '/^temp:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

        return [
            'slide_image_ref' => [
                Rule::requiredIf(fn () => $this->input('type', SliderItem::TYPE_IMAGE) === SliderItem::TYPE_IMAGE && ! $isUpdate),
                'nullable',
                'string',
                $isUpdate
                    ? 'regex:'.SliderItemService::IMAGE_REF_PATTERN
                    : 'regex:'.$tempPattern,
            ],
            'remove_slide_image' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareSliderItemImageInput(): void
    {
        if ($this->input('slide_image_ref') === '') {
            $this->merge(['slide_image_ref' => null]);
        }
    }

    /**
     * @return array<string, string>
     */
    protected function sliderItemImageMessages(): array
    {
        return [
            'slide_image_ref.required' => __('Please upload a slide image.'),
            'slide_image_ref.regex' => __('The slide image is invalid. Please upload again.'),
            'video_url.required' => __('Please enter a video URL.'),
            'video_url.url' => __('Please enter a valid video URL.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function sliderItemImageAttributes(): array
    {
        return [
            'slide_image_ref' => __('slide image'),
            'video_url' => __('video URL'),
        ];
    }

    protected function validateSliderItemFields(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $type = $this->input('type', SliderItem::TYPE_IMAGE);

            if ($type === SliderItem::TYPE_VIDEO && ! $this->filled('video_url')) {
                $validator->errors()->add('video_url', __('Please enter a video URL.'));
            }

            if ($type === SliderItem::TYPE_IMAGE && $this->boolean('remove_slide_image') && ! $this->filled('slide_image_ref')) {
                $validator->errors()->add('slide_image_ref', __('Please upload a slide image.'));
            }
        });
    }
}
