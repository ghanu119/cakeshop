<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCakeWeightRequest;
use App\Http\Requests\Admin\UpdateCakeWeightRequest;
use App\Models\Setting;
use App\Models\VariantOptionValue;
use App\Services\VariantOptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CakeWeightController extends Controller
{
    public function __construct(
        private VariantOptionService $variantOptionService
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Setting::class);
        $type = $this->variantOptionService->ensureWeightType();
        $values = $type->values()
            ->orderBy('sort_order')
            ->orderBy('grams')
            ->paginate(20)
            ->withQueryString();

        return view('admin.cake-weights.index', compact('type', 'values'));
    }

    public function create(): View
    {
        $this->authorize('viewAny', Setting::class);
        $this->variantOptionService->ensureWeightType();
        $weight = null;

        return view('admin.cake-weights.create', compact('weight'));
    }

    public function store(StoreCakeWeightRequest $request): RedirectResponse
    {
        $type = $this->variantOptionService->ensureWeightType();
        $this->variantOptionService->createOrUpdateValue(null, $type, $request->validated());

        return redirect()->route('admin.cake-weights.index')
            ->with('status', __('Weight option added.'));
    }

    public function edit(VariantOptionValue $cake_weight): View
    {
        $this->authorize('viewAny', Setting::class);
        $type = $this->variantOptionService->ensureWeightType();
        abort_unless($cake_weight->variant_option_type_id === $type->id, 404);

        return view('admin.cake-weights.edit', ['weight' => $cake_weight]);
    }

    public function update(UpdateCakeWeightRequest $request, VariantOptionValue $cake_weight): RedirectResponse
    {
        $type = $this->variantOptionService->ensureWeightType();
        abort_unless($cake_weight->variant_option_type_id === $type->id, 404);
        $this->variantOptionService->createOrUpdateValue($cake_weight, $type, $request->validated());

        return redirect()->route('admin.cake-weights.index')
            ->with('status', __('Weight option updated.'));
    }

    public function destroy(VariantOptionValue $cake_weight): RedirectResponse
    {
        $this->authorize('viewAny', Setting::class);
        $type = $this->variantOptionService->ensureWeightType();
        abort_unless($cake_weight->variant_option_type_id === $type->id, 404);

        if (\App\Models\ProductVariantSelection::where('variant_option_value_id', $cake_weight->id)->exists()) {
            return back()->withErrors(['weight' => __('This weight is used on products. Set it to inactive instead of deleting.')]);
        }

        $cake_weight->delete();

        return redirect()->route('admin.cake-weights.index')
            ->with('status', __('Weight option removed.'));
    }
}
