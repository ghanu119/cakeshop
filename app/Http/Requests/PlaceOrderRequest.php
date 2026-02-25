<?php

namespace App\Http\Requests;

use App\Services\OrderService;
use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $orderService = app(OrderService::class);
        $rules = $orderService->deliveryAtRules();
        $after = $rules['after']->format('Y-m-d\TH:i');
        $before = $rules['before']->format('Y-m-d\TH:i');

        return [
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['nullable', 'email'],
            'guest_phone' => ['required', 'string', 'max:50'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
            'message_on_cake' => ['nullable', 'string', 'max:500'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'delivery_at' => ['required', 'date', 'after_or_equal:'.$after, 'before_or_equal:'.$before],
        ];
    }
}
