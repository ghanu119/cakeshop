<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdateCustomerProfileRequest;
use App\Models\User;
use App\Models\User\UserGender;
use App\Services\CustomerDeletionService;
use App\Services\CustomerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private CustomerService $customerService
    ) {}

    public function index(Request $request): View
    {
        $customer = $request->user();
        $recentOrders = $this->customerService->recentOrdersForCustomer($customer);

        return view('account.dashboard', compact('customer', 'recentOrders'));
    }
}
