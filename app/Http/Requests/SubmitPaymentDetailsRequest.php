<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class SubmitPaymentDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order instanceof Order && $order->guest_phone === $this->input('phone');
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
