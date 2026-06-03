<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVariantOptionTypeRequest;
use App\Http\Requests\Admin\UpdateVariantOptionTypeRequest;
use App\Models\Setting;
use App\Models\VariantOptionType;
use App\Services\VariantOptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VariantOptionTypeController extends Controller
{
    public function __construct(
        private VariantOptionService $variantOptionService
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Setting::class);
        $types = $this->variantOptionService->listTypes(request());

        return view('admin.variant-options.types.index', compact('types'));
    }

    public function create(): View
    {
        $this->authorize('viewAny', Setting::class);
        $type = null;

        return view('admin.variant-options.types.create', compact('type'));
    }

    public function store(StoreVariantOptionTypeRequest $request): RedirectResponse
    {
        $this->variantOptionService->createOrUpdateType(null, $request->validated());

        return redirect()->route('admin.variant-option-types.index')->with('status', __('Option type created.'));
    }

    public function edit(VariantOptionType $variant_option_type): View
    {
        $this->authorize('viewAny', Setting::class);

        return view('admin.variant-options.types.edit', ['type' => $variant_option_type]);
    }

    public function update(UpdateVariantOptionTypeRequest $request, VariantOptionType $variant_option_type): RedirectResponse
    {
        $this->variantOptionService->createOrUpdateType($variant_option_type, $request->validated());

        return redirect()->route('admin.variant-option-types.index')->with('status', __('Option type updated.'));
    }

    public function destroy(VariantOptionType $variant_option_type): RedirectResponse
    {
        $this->authorize('viewAny', Setting::class);

        if ($variant_option_type->values()->exists()) {
            return back()->withErrors(['type' => __('Cannot delete: values exist. Set inactive instead.')]);
        }

        $variant_option_type->delete();

        return redirect()->route('admin.variant-option-types.index')->with('status', __('Option type deleted.'));
    }
}
