<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceablePincodeRequest;
use App\Http\Requests\Admin\UpdateServiceablePincodeRequest;
use App\Models\ServiceablePincode;
use App\Services\ServiceablePincodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceablePincodeController extends Controller
{
    public function __construct(
        private ServiceablePincodeService $pincodeService
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', ServiceablePincode::class);
        $pincodes = $this->pincodeService->list(request());

        return view('admin.serviceable-pincodes.index', compact('pincodes'));
    }

    public function create(): View
    {
        $this->authorize('create', ServiceablePincode::class);
        $pincode = null;

        return view('admin.serviceable-pincodes.create', compact('pincode'));
    }

    public function store(StoreServiceablePincodeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $this->pincodeService->createOrUpdate(null, $data);

        return redirect()->route('admin.serviceable-pincodes.index')->with('status', __('Serviceable pincode created.'));
    }

    public function edit(ServiceablePincode $serviceablePincode): View
    {
        $this->authorize('update', $serviceablePincode);

        return view('admin.serviceable-pincodes.edit', ['pincode' => $serviceablePincode]);
    }

    public function update(UpdateServiceablePincodeRequest $request, ServiceablePincode $serviceablePincode): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $this->pincodeService->createOrUpdate($serviceablePincode, $data);

        return redirect()->route('admin.serviceable-pincodes.index')->with('status', __('Serviceable pincode updated.'));
    }

    public function destroy(ServiceablePincode $serviceablePincode): RedirectResponse
    {
        $this->authorize('delete', $serviceablePincode);
        $this->pincodeService->delete($serviceablePincode);

        return redirect()->route('admin.serviceable-pincodes.index')->with('status', __('Serviceable pincode deleted.'));
    }
}
