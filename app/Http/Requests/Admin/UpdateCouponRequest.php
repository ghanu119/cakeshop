<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesCoupon;
use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCouponRequest extends FormRequest
{
    use ValidatesCoupon;

    public function authorize(): bool
    {
        /** @var Coupon $coupon */
        $coupon = $this->route('coupon');

        return $this->user()?->can('update', $coupon) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareCouponValidation();
    }

    public function rules(): array
    {
        /** @var Coupon $coupon */
        $coupon = $this->route('coupon');

        return $this->couponRules($coupon);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->boolean('auto_apply')) {
                return;
            }

            if ($this->input('product_scope') === Coupon::PRODUCT_SCOPE_PRODUCTS
                && empty($this->input('product_ids'))) {
                $validator->errors()->add('product_ids', __('Select at least one product.'));
            }

            if ($this->input('product_scope') === Coupon::PRODUCT_SCOPE_CATEGORIES
                && empty($this->input('category_ids'))) {
                $validator->errors()->add('category_ids', __('Select at least one category.'));
            }

            if ($this->input('user_scope') === Coupon::USER_SCOPE_USERS
                && empty($this->input('user_ids'))) {
                $validator->errors()->add('user_ids', __('Select at least one customer.'));
            }
        });
    }
}
