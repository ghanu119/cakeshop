<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CustomerContext;
use App\Support\AuthGuards;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    public function __construct(
        private CustomerContext $customerContext
    ) {}

    public function stop(Request $request): RedirectResponse
    {
        $admin = $request->user(AuthGuards::STAFF);
        abort_unless($admin?->can('customers.impersonate'), 403);

        $customerId = $this->customerContext->stopImpersonation();

        if ($customerId) {
            return redirect()->route('admin.customers.show', $customerId)
                ->with('status', __('Stopped shopping as customer.'));
        }

        return redirect()->route('admin.customers.index');
    }
}
