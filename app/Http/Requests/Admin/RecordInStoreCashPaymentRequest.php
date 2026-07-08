<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class RecordInStoreCashPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Order|null $order */
        $order = $this->route('order');

        return $order !== null && $this->user()?->can('update', $order);
    }

    public function rules(): array
    {
        /** @var Order $order */
        $order = $this->route('order');
        $max = $order->balanceDue();

        return [
            'amount_received' => ['required', 'numeric', 'min:0.01', 'max:'.$max],
        ];
    }

    public function messages(): array
    {
        return [
            'amount_received.max' => __('Amount cannot exceed the balance due of :amount.', [
                'amount' => '₹ '.number_format((float) $this->route('order')->balanceDue(), 2),
            ]),
        ];
    }
}
