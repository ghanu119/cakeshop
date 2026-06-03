<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('orders.update');
    }

    public function rules(): array
    {
        $allowedStatuses = $this->user()->hasRole('Admin')
            ? ['pending', 'processing', 'completed', 'cancelled']
            : ['completed', 'cancelled'];

        $rules = [
            'order_status' => ['required', 'string', 'in:'.implode(',', $allowedStatuses)],
        ];

        if ($this->user()->hasRole('Admin') && $this->input('order_status') === 'processing') {
            $rules['preparation_at'] = ['required', 'date'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->user()->hasRole('Admin')) {
                if ($this->filled('preparation_at')) {
                    $validator->errors()->add(
                        'preparation_at',
                        __('Only an administrator can set the preparation time.')
                    );
                }

                return;
            }

            if ($this->input('order_status') !== 'processing') {
                return;
            }

            if ($validator->errors()->has('preparation_at')) {
                return;
            }

            /** @var Order|null $order */
            $order = $this->route('order');
            if (! $order) {
                return;
            }

            $prepRules = app(OrderService::class)->preparationAtRules($order);
            $tz = $prepRules['timezone'];
            $preparationAt = Carbon::parse($this->input('preparation_at'), $tz);

            if ($preparationAt->lt($prepRules['min'])) {
                $validator->errors()->add(
                    'preparation_at',
                    __('Preparation time must be now or in the future.')
                );
            }

            if ($prepRules['max'] !== null && $preparationAt->gt($prepRules['max'])) {
                $validator->errors()->add(
                    'preparation_at',
                    __('Preparation time must be on or before the scheduled delivery.')
                );
            }
        });
    }
}
