<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Models\Product;
use App\Services\CustomerAuthService;
use App\Services\CustomerContext;
use App\Services\OrderService;
use App\Services\ProductVariantService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Product|null $product */
        $product = $this->route('product');
        $messageMax = $product?->messageOnCakeMaxLength() ?? Order::defaultMessageOnCakeMaxLength();

        $customer = app(CustomerContext::class)->effectiveCustomer();
        $isImpersonating = app(CustomerContext::class)->isImpersonating();

        $rules = [
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => [$isImpersonating ? 'nullable' : 'required', 'email', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:50'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
            'message_on_cake' => ['nullable', 'string', 'max:'.$messageMax],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'delivery_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'product_variant_id' => ['nullable', 'integer'],
            'flavor_id' => ['nullable', 'integer'],
        ];

        if (uses_better_buns_checkout()) {
            $rules['fulfillment_type'] = ['required', 'string', 'in:takeaway,delivery'];
            $rules['delivery_address'] = ['nullable', 'string', 'max:1000', 'required_if:fulfillment_type,delivery'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Product|null $product */
            $product = $this->route('product');
            if (! $product) {
                return;
            }

            $deliveryAt = (string) $this->input('delivery_at', '');
            if ($deliveryAt !== '') {
                $deliveryError = app(OrderService::class)->validateDeliveryAtForProduct($product, $deliveryAt);
                if ($deliveryError !== null) {
                    $validator->errors()->add('delivery_at', $deliveryError);
                }
            }

            $customer = app(CustomerContext::class)->effectiveCustomer();

            if ($customer === null) {
                $verifiedEmail = app(CustomerAuthService::class)->verifiedEmail();
                $submittedEmail = strtolower(trim((string) $this->input('guest_email', '')));

                if ($verifiedEmail === null || $verifiedEmail !== $submittedEmail) {
                    $validator->errors()->add(
                        'guest_email',
                        __('Please verify your email with the code we sent before placing your order.')
                    );
                }
            }

            $variantService = app(ProductVariantService::class);

            if ($variantService->hasVariants($product)) {
                $variantId = $this->input('product_variant_id');
                if (! $variantId) {
                    $validator->errors()->add('product_variant_id', __('Please select a weight.'));
                } else {
                    try {
                        $variantService->findVariantForProduct($product, (int) $variantId);
                    } catch (\Throwable) {
                        $validator->errors()->add('product_variant_id', __('Invalid weight selection.'));
                    }
                }
            }

            if ($product->hasFlavors()) {
                $flavorId = $this->input('flavor_id');
                if (! $flavorId) {
                    $validator->errors()->add('flavor_id', __('Please select a flavor.'));
                } elseif (! $product->flavors()->active()->whereKey($flavorId)->exists()) {
                    $validator->errors()->add('flavor_id', __('Invalid flavor selection.'));
                }
            }
        });
    }
}
