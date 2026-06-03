<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVariantOptionValueRequest;
use App\Http\Requests\Admin\UpdateVariantOptionValueRequest;
use App\Models\Setting;
use App\Models\VariantOptionType;
use App\Models\VariantOptionValue;
use App\Services\VariantOptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VariantOptionValueController extends Controller
{
    public function __construct(
        private VariantOptionService $variantOptionService
    ) {}

    public function index(VariantOptionType $variant_option_type): View
    {
        $this->authorize('viewAny', Setting::class);
        $type = $variant_option_type;
        $values = $this->variantOptionService->listValues($type, request());

        return view('admin.variant-options.values.index', compact('type', 'values'));
    }

    public function create(VariantOptionType $variant_option_type): View
    {
        $this->authorize('viewAny', Setting::class);
        $type = $variant_option_type;
        $value = null;

        return view('admin.variant-options.values.create', compact('type', 'value'));
    }

    public function store(StoreVariantOptionValueRequest $request, VariantOptionType $variant_option_type): RedirectResponse
    {
        $this->variantOptionService->createOrUpdateValue(null, $variant_option_type, $request->validated());

        return redirect()->route('admin.variant-option-types.values.index', $variant_option_type)
            ->with('status', __('Option value created.'));
    }

    public function edit(VariantOptionType $variant_option_type, VariantOptionValue $value): View
    {
        $this->authorize('viewAny', Setting::class);
        $type = $variant_option_type;
        abort_unless($value->variant_option_type_id === $type->id, 404);

        return view('admin.variant-options.values.edit', compact('type', 'value'));
    }

    public function update(UpdateVariantOptionValueRequest $request, VariantOptionType $variant_option_type, VariantOptionValue $value): RedirectResponse
    {
        abort_unless($value->variant_option_type_id === $variant_option_type->id, 404);
        $this->variantOptionService->createOrUpdateValue($value, $variant_option_type, $request->validated());

        return redirect()->route('admin.variant-option-types.values.index', $variant_option_type)
            ->with('status', __('Option value updated.'));
    }

    public function destroy(VariantOptionType $variant_option_type, VariantOptionValue $value): RedirectResponse
    {
        $this->authorize('viewAny', Setting::class);
        abort_unless($value->variant_option_type_id === $variant_option_type->id, 404);

        if (\App\Models\ProductVariantSelection::where('variant_option_value_id', $value->id)->exists()) {
            return back()->withErrors(['value' => __('Cannot delete: used by product variants. Set inactive instead.')]);
        }

        $value->delete();

        return redirect()->route('admin.variant-option-types.values.index', $variant_option_type)
            ->with('status', __('Option value deleted.'));
    }
}
