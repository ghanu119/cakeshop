<?php

namespace App\View\Composers;

use App\Services\CouponService;
use Illuminate\View\View;

class StorefrontPromoComposer
{
    public function __construct(
        private CouponService $couponService
    ) {}

    public function compose(View $view): void
    {
        $coupon = $this->couponService->soleActiveAutoApplyCoupon();

        $view->with('storefrontPromoCoupon', $coupon);
        $view->with('storefrontPromoBadge', $coupon ? $this->couponService->badgeText($coupon) : null);
    }
}
