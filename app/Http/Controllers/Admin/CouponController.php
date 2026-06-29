<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use App\Services\CouponService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function __construct(
        private CouponService $couponService
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Coupon::class);
        $coupons = $this->couponService->list(request());

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create(): View
    {
        $this->authorize('create', Coupon::class);

        return view('admin.coupons.create', $this->formData(null));
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        $this->couponService->createOrUpdate(null, $request->validated());

        return redirect()->route('admin.coupons.index')->with('status', __('Coupon created.'));
    }

    public function edit(Coupon $coupon): View
    {
        $this->authorize('update', $coupon);
        $coupon->load(['products', 'categories', 'users']);

        return view('admin.coupons.edit', $this->formData($coupon));
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $this->couponService->createOrUpdate($coupon, $request->validated());

        return redirect()->route('admin.coupons.index')->with('status', __('Coupon updated.'));
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $this->authorize('delete', $coupon);
        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('status', __('Coupon deleted.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(?Coupon $coupon): array
    {
        return [
            'coupon' => $coupon,
            'products' => Product::query()->active()->orderBy('name_en')->get(['id', 'name_en']),
            'categories' => Category::query()->active()->orderBy('sort_order')->get(['id', 'name_en']),
            'customers' => User::query()
                ->customers()
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'phone']),
        ];
    }
}
