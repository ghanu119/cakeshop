<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function list(Request $request): LengthAwarePaginator
    {
        $query = Coupon::query();

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', "%{$term}%")
                    ->orWhere('label', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('auto_apply')) {
            $query->where('auto_apply', $request->boolean('auto_apply'));
        }

        return $query->orderByDesc('created_at')->paginate(15)->withQueryString();
    }

    public function createOrUpdate(?Coupon $coupon, array $data): Coupon
    {
        return DB::transaction(function () use ($coupon, $data) {
            $coupon = $coupon ?? new Coupon;

            $autoApply = ! empty($data['auto_apply']);

            $coupon->code = strtoupper(trim($data['code']));
            $coupon->label = $data['label'];
            $coupon->description = $data['description'] ?? null;
            $coupon->from_date = $data['from_date'];
            $coupon->to_date = $data['to_date'];
            $coupon->discount_type = $data['discount_type'];
            $coupon->discount_amount = $data['discount_amount'];
            $coupon->max_discount_amount = $data['discount_type'] === Coupon::DISCOUNT_PERCENTAGE
                ? ($data['max_discount_amount'] ?? null)
                : null;
            $coupon->status = $data['status'] ?? Coupon::STATUS_ACTIVE;
            $coupon->auto_apply = $autoApply;
            // An auto-apply coupon can never be secret: it's meant to apply automatically to every
            // qualifying order, so hiding it from the picker would be contradictory. Enforce this
            // here too (not just in the FormRequest) since this is the single write path for coupons.
            $coupon->is_secret = $autoApply ? false : ! empty($data['is_secret']);
            $coupon->min_order_amount = isset($data['min_order_amount']) && $data['min_order_amount'] !== ''
                ? $data['min_order_amount']
                : null;

            if ($autoApply) {
                $coupon->product_scope = Coupon::PRODUCT_SCOPE_ALL;
                $coupon->user_scope = Coupon::USER_SCOPE_ALL;
            } else {
                $coupon->product_scope = $data['product_scope'] ?? Coupon::PRODUCT_SCOPE_ALL;
                $coupon->user_scope = $data['user_scope'] ?? Coupon::USER_SCOPE_ALL;
            }

            $coupon->save();

            if ($autoApply) {
                $coupon->products()->sync([]);
                $coupon->categories()->sync([]);
                $coupon->users()->sync([]);
            } else {
                $this->syncProducts($coupon, $data);
                $this->syncCategories($coupon, $data);
                $this->syncUsers($coupon, $data);
            }

            return $coupon;
        });
    }

    private function syncProducts(Coupon $coupon, array $data): void
    {
        if ($coupon->product_scope === Coupon::PRODUCT_SCOPE_PRODUCTS) {
            $coupon->products()->sync($data['product_ids'] ?? []);
        } else {
            $coupon->products()->sync([]);
        }
    }

    private function syncCategories(Coupon $coupon, array $data): void
    {
        if ($coupon->product_scope === Coupon::PRODUCT_SCOPE_CATEGORIES) {
            $coupon->categories()->sync($data['category_ids'] ?? []);
        } else {
            $coupon->categories()->sync([]);
        }
    }

    private function syncUsers(Coupon $coupon, array $data): void
    {
        if ($coupon->user_scope === Coupon::USER_SCOPE_USERS) {
            $coupon->users()->sync($data['user_ids'] ?? []);
        } else {
            $coupon->users()->sync([]);
        }
    }

    public function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        if ($coupon->discount_type === Coupon::DISCOUNT_FIXED) {
            return round(min((float) $coupon->discount_amount, $subtotal), 2);
        }

        $raw = $subtotal * ((float) $coupon->discount_amount / 100);
        $cap = $coupon->max_discount_amount !== null ? (float) $coupon->max_discount_amount : $raw;

        return round(min($raw, $cap, $subtotal), 2);
    }

    public function isEligible(Coupon $coupon, Product $product, ?User $user, float $subtotal, ?Carbon $at = null): bool
    {
        if (! $coupon->isActive() || ! $coupon->isWithinDateRange($at)) {
            return false;
        }

        if ($coupon->min_order_amount !== null && $subtotal < (float) $coupon->min_order_amount) {
            return false;
        }

        if (! $this->matchesProductScope($coupon, $product)) {
            return false;
        }

        if (! $this->matchesUserScope($coupon, $user)) {
            return false;
        }

        return true;
    }

    private function matchesProductScope(Coupon $coupon, Product $product): bool
    {
        return match ($coupon->product_scope) {
            Coupon::PRODUCT_SCOPE_ALL => true,
            Coupon::PRODUCT_SCOPE_PRODUCTS => $coupon->products()->whereKey($product->id)->exists(),
            Coupon::PRODUCT_SCOPE_CATEGORIES => $product->category_id
                && $coupon->categories()->whereKey($product->category_id)->exists(),
            default => false,
        };
    }

    private function matchesUserScope(Coupon $coupon, ?User $user): bool
    {
        return match ($coupon->user_scope) {
            Coupon::USER_SCOPE_ALL => true,
            Coupon::USER_SCOPE_USERS => $user !== null && $coupon->users()->whereKey($user->id)->exists(),
            default => false,
        };
    }

    /**
     * @return array{message: string, reason: string}|null
     */
    private function ineligibilityDetails(Coupon $coupon, Product $product, ?User $user, float $subtotal, Carbon $at): ?array
    {
        if (! $coupon->isActive() || ! $coupon->isWithinDateRange($at)) {
            return [
                'message' => __('This coupon code is invalid or not applicable to your order.'),
                'reason' => 'invalid',
            ];
        }

        if ($coupon->min_order_amount !== null && $subtotal < (float) $coupon->min_order_amount) {
            return [
                'message' => __('This order does not meet the minimum amount for this coupon.'),
                'reason' => 'min_order_amount',
            ];
        }

        if (! $this->matchesProductScope($coupon, $product)) {
            return [
                'message' => __('This coupon code is invalid or not applicable to your order.'),
                'reason' => 'invalid',
            ];
        }

        if (! $this->matchesUserScope($coupon, $user)) {
            if ($coupon->user_scope === Coupon::USER_SCOPE_USERS && $user === null) {
                return [
                    'message' => __('Sign in to your account, then apply the code again.'),
                    'reason' => 'sign_in_required',
                ];
            }

            return [
                'message' => __('This coupon is not available for your account.'),
                'reason' => 'not_for_account',
            ];
        }

        return null;
    }

    private function ineligibilityReasonForInput(
        Product $product,
        ?User $user,
        float $subtotal,
        ?string $manualCode,
        ?int $selectedCouponId,
    ): ?string {
        $at = $this->nowInSiteTimezone();
        $coupon = null;

        if ($manualCode !== null && trim($manualCode) !== '') {
            $coupon = Coupon::query()->where('code', strtoupper(trim($manualCode)))->first();
        } elseif ($selectedCouponId !== null && $selectedCouponId > 0) {
            $coupon = Coupon::query()->find($selectedCouponId);
        }

        if ($coupon === null) {
            return 'invalid';
        }

        $details = $this->ineligibilityDetails($coupon, $product, $user, $subtotal, $at);

        return $details['reason'] ?? null;
    }

    /**
     * @return array{coupon: Coupon, discount_amount: float}|null
     */
    public function resolveForOrder(
        Product $product,
        ?User $user,
        float $subtotal,
        ?string $manualCode = null,
        ?int $selectedCouponId = null,
    ): ?array {
        $at = $this->nowInSiteTimezone();

        $manualCode = $manualCode !== null ? strtoupper(trim($manualCode)) : null;

        if ($manualCode !== null && $manualCode !== '') {
            return $this->resolveManualCode($manualCode, $product, $user, $subtotal, $at);
        }

        if ($selectedCouponId !== null && $selectedCouponId > 0) {
            return $this->resolveSelectedCoupon($selectedCouponId, $product, $user, $subtotal, $at);
        }

        $universal = $this->eligibleUniversalCoupons($product, $user, $subtotal, $at);
        $autoApplyCoupons = $universal->filter(fn (Coupon $coupon) => $coupon->auto_apply);
        $coupon = $this->pickLowestDiscountAutoApplyCoupon($autoApplyCoupons, $subtotal);

        if ($coupon !== null) {
            return $this->buildResult($coupon, $subtotal);
        }

        return null;
    }

    /**
     * @return array{coupon: Coupon, discount_amount: float}
     */
    private function resolveManualCode(string $code, Product $product, ?User $user, float $subtotal, Carbon $at): array
    {
        $coupon = Coupon::query()->where('code', $code)->first();

        if ($coupon === null) {
            throw ValidationException::withMessages([
                'coupon_code' => [__('This coupon code is invalid or not applicable to your order.')],
            ]);
        }

        $details = $this->ineligibilityDetails($coupon, $product, $user, $subtotal, $at);
        if ($details !== null) {
            throw ValidationException::withMessages([
                'coupon_code' => [$details['message']],
            ]);
        }

        return $this->buildResult($coupon, $subtotal);
    }

    /**
     * @return array{coupon: Coupon, discount_amount: float}
     */
    private function resolveSelectedCoupon(int $couponId, Product $product, ?User $user, float $subtotal, Carbon $at): array
    {
        $coupon = Coupon::query()->find($couponId);

        if ($coupon === null) {
            throw ValidationException::withMessages([
                'coupon_id' => [__('The selected coupon is invalid or not applicable to your order.')],
            ]);
        }

        $details = $this->ineligibilityDetails($coupon, $product, $user, $subtotal, $at);
        if ($details !== null) {
            throw ValidationException::withMessages([
                'coupon_id' => [$details['message']],
            ]);
        }

        return $this->buildResult($coupon, $subtotal);
    }

    /**
     * @return array{coupon: Coupon, discount_amount: float}
     */
    private function buildResult(Coupon $coupon, float $subtotal): array
    {
        return [
            'coupon' => $coupon,
            'discount_amount' => $this->calculateDiscount($coupon, $subtotal),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function listUniversalCouponsForCheckout(Product $product, ?User $user, float $subtotal): Collection
    {
        $at = $this->nowInSiteTimezone();

        return $this->eligibleUniversalCoupons($product, $user, $subtotal, $at)
            ->map(fn (Coupon $coupon) => $this->formatCheckoutCoupon($coupon, $subtotal))
            ->sortBy(fn (array $coupon) => [
                -($coupon['discount_amount'] ?? 0),
                -($coupon['auto_apply'] ? 1 : 0),
                $coupon['id'],
            ])
            ->values();
    }

    public function defaultCheckoutCouponId(Collection $universalCoupons): ?int
    {
        $coupon = $this->lowestDiscountAutoApplyFromCheckoutList($universalCoupons);

        return $coupon !== null ? (int) $coupon['id'] : null;
    }

    /**
     * Auto-apply coupon with the smallest discount amount for the current subtotal.
     *
     * @return array<string, mixed>|null
     */
    public function lowestDiscountAutoApplyFromCheckoutList(Collection $universalCoupons): ?array
    {
        return $universalCoupons
            ->filter(fn (array $coupon) => $coupon['auto_apply'])
            ->sortBy(fn (array $coupon) => [
                ($coupon['discount_amount'] ?? 0),
                $coupon['id'],
            ])
            ->first();
    }

    /**
     * Coupons eligible to appear in the public checkout picker: universal coupons, plus
     * product/category-scoped coupons that actually match the current product. Secret
     * coupons and user-scoped coupons are excluded from this list — both remain redeemable
     * via manual code entry (see resolveManualCode()/matchesProductScope()), which is
     * unaffected by this listing query.
     *
     * @return Collection<int, Coupon>
     */
    private function eligibleUniversalCoupons(Product $product, ?User $user, float $subtotal, Carbon $at): Collection
    {
        return Coupon::query()
            ->active()
            ->where('is_secret', false)
            ->where('user_scope', Coupon::USER_SCOPE_ALL)
            ->where(function ($query) use ($product) {
                $query->where('product_scope', Coupon::PRODUCT_SCOPE_ALL)
                    ->orWhere(function ($query) use ($product) {
                        $query->where('product_scope', Coupon::PRODUCT_SCOPE_PRODUCTS)
                            ->whereHas('products', fn ($q) => $q->whereKey($product->id));
                    })
                    ->orWhere(function ($query) use ($product) {
                        $query->where('product_scope', Coupon::PRODUCT_SCOPE_CATEGORIES)
                            ->when(
                                $product->category_id,
                                fn ($q) => $q->whereHas('categories', fn ($q) => $q->whereKey($product->category_id)),
                                fn ($q) => $q->whereRaw('1 = 0'),
                            );
                    });
            })
            ->get()
            ->filter(fn (Coupon $coupon) => $coupon->isWithinDateRange($at))
            ->filter(fn (Coupon $coupon) => $this->isEligible($coupon, $product, $user, $subtotal, $at))
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatCheckoutCoupon(Coupon $coupon, float $subtotal): array
    {
        return [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'label' => $coupon->label,
            'description' => $coupon->description,
            'badge_text' => $this->badgeText($coupon),
            'discount_amount' => $this->calculateDiscount($coupon, $subtotal),
            'max_cap' => $coupon->max_discount_amount !== null ? (float) $coupon->max_discount_amount : null,
            'auto_apply' => $coupon->auto_apply,
        ];
    }

    public function soleActiveAutoApplyCoupon(): ?Coupon
    {
        $at = $this->nowInSiteTimezone();

        $coupons = $this->activeAutoApplyCoupons($at);

        return $coupons->count() === 1 ? $coupons->first() : null;
    }

    /**
     * @return Collection<int, Coupon>
     */
    private function activeAutoApplyCoupons(?Carbon $at = null): Collection
    {
        $at ??= $this->nowInSiteTimezone();

        return Coupon::query()
            ->active()
            ->where('auto_apply', true)
            ->get()
            ->filter(fn (Coupon $coupon) => $coupon->isWithinDateRange($at))
            ->values();
    }

    public function bestAutoApplyCoupon(
        Product $product,
        ?User $user,
        float $subtotal,
        ?Collection $preloadedAutoApplyCoupons = null,
    ): ?Coupon {
        $at = $this->nowInSiteTimezone();
        $candidates = ($preloadedAutoApplyCoupons ?? $this->activeAutoApplyCoupons($at))
            ->filter(fn (Coupon $coupon) => $this->isEligible($coupon, $product, $user, $subtotal, $at))
            ->values();

        return $this->pickLowestDiscountAutoApplyCoupon($candidates, $subtotal);
    }

    /**
     * Among auto-apply coupons, pick the one with the smallest discount amount.
     *
     * @param  Collection<int, Coupon>  $candidates
     */
    private function pickLowestDiscountAutoApplyCoupon(Collection $candidates, float $subtotal): ?Coupon
    {
        if ($candidates->isEmpty()) {
            return null;
        }

        return $candidates
            ->sortBy(fn (Coupon $coupon) => [
                $this->calculateDiscount($coupon, $subtotal),
                $coupon->id,
            ])
            ->first();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function storefrontPromoForProduct(
        Product $product,
        ?User $user,
        ?Collection $preloadedAutoApplyCoupons = null,
    ): ?array {
        $originalPrice = (float) $product->price;
        $subtotal = $originalPrice;

        $coupon = $this->bestAutoApplyCoupon($product, $user, $subtotal, $preloadedAutoApplyCoupons);
        if ($coupon === null) {
            return null;
        }

        $discountAmount = $this->calculateDiscount($coupon, $subtotal);

        return [
            'original_price' => $originalPrice,
            'discounted_price' => max(0, $originalPrice - $discountAmount),
            'discount_amount' => $discountAmount,
            'badge_text' => $this->badgeText($coupon),
            'headline' => $coupon->label,
            'description' => $coupon->description,
            'coupon_discount' => [
                'type' => $coupon->discount_type,
                'amount' => (float) $coupon->discount_amount,
                'max_cap' => $coupon->max_discount_amount !== null ? (float) $coupon->max_discount_amount : null,
            ],
        ];
    }

    public function attachStorefrontPromoToProducts(iterable $products, ?User $user = null): void
    {
        $autoApplyCoupons = $this->activeAutoApplyCoupons();

        foreach ($products as $product) {
            $product->setAttribute(
                'storefront_promo',
                $this->storefrontPromoForProduct($product, $user, $autoApplyCoupons),
            );
        }
    }

    public function badgeText(Coupon $coupon): string
    {
        if ($coupon->discount_type === Coupon::DISCOUNT_PERCENTAGE) {
            return (int) $coupon->discount_amount.'% OFF';
        }

        $symbol = $this->currencySymbol();

        return $symbol.number_format((float) $coupon->discount_amount, 0).' OFF';
    }

    public function currencySymbol(): string
    {
        $currency = settings('currency') ?? 'INR';

        return $currency === 'INR' ? '₹' : $currency.' ';
    }

    /**
     * Validate coupon for AJAX preview.
     *
     * @return array{valid: bool, discount_amount: float, label: string|null, message: string|null, reason: string|null}
     */
    public function validateForPreview(
        Product $product,
        ?User $user,
        float $subtotal,
        ?string $manualCode = null,
        ?int $selectedCouponId = null,
    ): array {
        try {
            $result = $this->resolveForOrder($product, $user, $subtotal, $manualCode, $selectedCouponId);

            if ($result === null) {
                return [
                    'valid' => false,
                    'discount_amount' => 0,
                    'label' => null,
                    'message' => null,
                    'max_cap' => null,
                    'coupon_code' => null,
                    'reason' => null,
                ];
            }

            $coupon = $result['coupon'];

            return [
                'valid' => true,
                'discount_amount' => $result['discount_amount'],
                'label' => $coupon->label,
                'message' => $this->badgeText($coupon),
                'max_cap' => $coupon->max_discount_amount !== null ? (float) $coupon->max_discount_amount : null,
                'coupon_code' => $coupon->code,
                'reason' => null,
            ];
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first();

            return [
                'valid' => false,
                'discount_amount' => 0,
                'label' => null,
                'message' => $message,
                'max_cap' => null,
                'coupon_code' => null,
                'reason' => $this->ineligibilityReasonForInput($product, $user, $subtotal, $manualCode, $selectedCouponId),
            ];
        }
    }

    private function nowInSiteTimezone(): Carbon
    {
        $tz = settings('timezone') ?? 'Asia/Kolkata';

        return now($tz);
    }
}
