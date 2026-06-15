<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Models\User;
use App\Services\CustomerContext;
use App\Services\CustomerDeletionService;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerService $customerService,
        private CustomerContext $customerContext,
        private CustomerDeletionService $deletionService
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('customers.view'), 403);

        $customers = $this->customerService->list($request);
        $retentionDays = config('privacy.customer_retention_days', 90);

        return view('admin.customers.index', compact('customers', 'retentionDays'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('customers.create'), 403);

        $lookup = ['conflict' => false, 'match' => null];

        if (old('phone') || old('email')) {
            $lookup = $this->customerService->lookup(old('email'), old('phone'));
        }

        return view('admin.customers.create', compact('lookup'));
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can('customers.create'), 403);

        $lookup = $this->customerService->lookup(
            $request->input('email'),
            $request->input('phone')
        );

        if ($lookup['conflict'] ?? false) {
            return back()->withErrors(['phone' => $lookup['message']])->withInput();
        }

        if ($lookup['match'] ?? null) {
            return back()->withErrors([
                'phone' => __('A matching customer already exists. Use Shop as customer below.'),
            ])->withInput();
        }

        $customer = $this->customerService->create($request->validated(), $request->user());

        return redirect()->route('admin.customers.show', $customer)->with('status', __('Customer created.'));
    }

    public function show(Request $request, User $customer): View
    {
        abort_unless($customer->isCustomer(), 404);
        abort_unless($request->user()->can('customers.view'), 403);

        $orders = $this->customerService->ordersForCustomer($customer);
        $retentionDays = config('privacy.customer_retention_days', 90);

        return view('admin.customers.show', compact('customer', 'orders', 'retentionDays'));
    }

    public function lookup(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('customers.view'), 403);

        return response()->json(
            $this->customerService->lookup(
                $request->query('email'),
                $request->query('phone')
            )
        );
    }

    public function impersonate(Request $request, User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer() && ! $customer->trashed(), 404);
        abort_unless($request->user()->can('customers.impersonate'), 403);

        $this->customerContext->startImpersonation($request->user(), $customer);

        return redirect()->route('products.index')
            ->with('status', __('You are now shopping for :name.', ['name' => $customer->name]));
    }

    public function destroy(Request $request, User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer() && ! $customer->trashed(), 404);
        abort_unless($request->user()->can('customers.delete'), 403);

        $this->deletionService->softDeleteCustomer($customer, User::DELETION_REASON_ADMIN);

        return redirect()->route('admin.customers.index', ['status' => 'deleted'])
            ->with('status', __('Customer deleted.'));
    }
}
