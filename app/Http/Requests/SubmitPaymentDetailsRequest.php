<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitPaymentDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $uuid = $this->route('uuid');
        $order = $uuid ? \App\Models\Order::where('uuid', $uuid)->first() : null;
        return $order && $order->guest_phone === $this->input('phone');
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'payment_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_made_at' => ['nullable', 'date'],
            'payment_proof' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
