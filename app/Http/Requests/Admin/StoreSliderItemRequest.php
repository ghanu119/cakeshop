<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesSliderItemImage;
use App\Models\SliderItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSliderItemRequest extends FormRequest
{
    use ValidatesSliderItemImage;

    public function authorize(): bool
    {
        return $this->user()?->can('slider_items.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareSliderItemImageInput();
    }

    public function rules(): array
    {
        return array_merge($this->sliderItemImageRules(), [
            'type' => ['required', Rule::in([SliderItem::TYPE_IMAGE, SliderItem::TYPE_VIDEO])],
            'title' => ['nullable', 'string', 'max:255'],
            'link' => ['nullable', 'string', 'max:2048'],
            'video_url' => ['nullable', 'string', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    public function messages(): array
    {
        return $this->sliderItemImageMessages();
    }

    public function attributes(): array
    {
        return $this->sliderItemImageAttributes();
    }

    public function withValidator($validator): void
    {
        $this->validateSliderItemFields($validator);
    }
}
