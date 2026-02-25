<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFeatureRequest;
use App\Http\Requests\Admin\UpdateFeatureRequest;
use App\Models\Feature;
use App\Services\FeatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeatureController extends Controller
{
    public function __construct(
        private FeatureService $featureService
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Feature::class);
        $features = $this->featureService->list(request());

        return view('admin.features.index', compact('features'));
    }

    public function create(): View
    {
        $this->authorize('create', Feature::class);
        $feature = null;

        return view('admin.features.create', compact('feature'));
    }

    public function store(StoreFeatureRequest $request): RedirectResponse
    {
        $this->featureService->createOrUpdate(null, $request->validated());

        return redirect()->route('admin.features.index')->with('status', __('Feature created.'));
    }

    public function edit(Feature $feature): View
    {
        $this->authorize('update', $feature);

        return view('admin.features.edit', compact('feature'));
    }

    public function update(UpdateFeatureRequest $request, Feature $feature): RedirectResponse
    {
        $this->featureService->createOrUpdate($feature, $request->validated());

        return redirect()->route('admin.features.index')->with('status', __('Feature updated.'));
    }

    public function destroy(Feature $feature): RedirectResponse
    {
        $this->authorize('delete', $feature);
        $feature->delete();

        return redirect()->route('admin.features.index')->with('status', __('Feature deleted.'));
    }
}
