<?php

namespace App\Http\Requests\Admin;

use App\Models\Slider;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSliderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $slider = $this->route('slider');

        return $slider instanceof Slider && ($this->user()?->can('update', $slider) ?? false);
    }

    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
        ];
    }
}
