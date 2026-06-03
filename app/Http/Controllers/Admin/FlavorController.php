<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFlavorRequest;
use App\Http\Requests\Admin\UpdateFlavorRequest;
use App\Models\Flavor;
use App\Services\FlavorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FlavorController extends Controller
{
    public function __construct(
        private FlavorService $flavorService
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Flavor::class);
        $flavors = $this->flavorService->list(request());

        return view('admin.flavors.index', compact('flavors'));
    }

    public function create(): View
    {
        $this->authorize('create', Flavor::class);
        $flavor = null;

        return view('admin.flavors.create', compact('flavor'));
    }

    public function store(StoreFlavorRequest $request): RedirectResponse
    {
        $this->flavorService->createOrUpdate(null, $request->validated());

        return redirect()->route('admin.flavors.index')->with('status', __('Flavor created.'));
    }

    public function edit(Flavor $flavor): View
    {
        $this->authorize('update', $flavor);

        return view('admin.flavors.edit', compact('flavor'));
    }

    public function update(UpdateFlavorRequest $request, Flavor $flavor): RedirectResponse
    {
        $this->flavorService->createOrUpdate($flavor, $request->validated());

        return redirect()->route('admin.flavors.index')->with('status', __('Flavor updated.'));
    }

    public function destroy(Flavor $flavor): RedirectResponse
    {
        $this->authorize('delete', $flavor);
        $flavor->delete();

        return redirect()->route('admin.flavors.index')->with('status', __('Flavor deleted.'));
    }
}
