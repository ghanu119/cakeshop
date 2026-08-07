<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Models\Coupon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesCoupon
{
    /**
     * @return array<string, mixed>
     */
    protected function couponRules(?Coupon $coupon = null): array
    {
        $couponId = $coupon?->id;

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('coupons', 'code')->ignore($couponId),
            ],
            'label' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'discount_type' => ['required', 'string', 'in:percentage,fixed'],
            'discount_amount' => ['required', 'numeric', 'min:0.01'],
            'max_discount_amount' => [
                'nullable',
                'required_if:discount_type,percentage',
                'numeric',
                'min:0.01',
            ],
            'status' => ['required', 'string', 'in:active,inactive'],
            'auto_apply' => ['nullable', 'boolean'],
            'is_secret' => ['nullable', 'boolean'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'product_scope' => ['nullable', 'string', 'in:all,products,categories'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'user_scope' => ['nullable', 'string', 'in:all,users'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ];
    }

    protected function assertNotAutoApplyAndSecret(Validator $validator): void
    {
        if ($this->boolean('auto_apply') && $this->boolean('is_secret')) {
            $validator->errors()->add('is_secret', __('A coupon cannot be both auto-applied and secret.'));
        }
    }

    protected function prepareCouponValidation(): void
    {
        if ($this->boolean('auto_apply')) {
            return;
        }

        if ($this->input('product_scope') === Coupon::PRODUCT_SCOPE_PRODUCTS) {
            $this->merge([
                'product_ids' => array_filter((array) $this->input('product_ids', [])),
            ]);
        }

        if ($this->input('product_scope') === Coupon::PRODUCT_SCOPE_CATEGORIES) {
            $this->merge([
                'category_ids' => array_filter((array) $this->input('category_ids', [])),
            ]);
        }

        if ($this->input('user_scope') === Coupon::USER_SCOPE_USERS) {
            $this->merge([
                'user_ids' => array_filter((array) $this->input('user_ids', [])),
            ]);
        }
    }

    /**
     * @param  callable(\Illuminate\Validation\Validator): void  $after
     *
     * @deprecated Use inline withValidator instead
     */
    protected function withCouponValidator(callable $after): void
    {
        // kept for trait compatibility if needed elsewhere
    }
}
