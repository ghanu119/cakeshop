<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdateCustomerProfileRequest;
use App\Models\User;
use App\Models\User\UserGender;
use App\Services\CustomerDeletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $customer = $request->user();

        return view('account.profile.edit', [
            'customer' => $customer,
            'genders' => UserGender::options(),
        ]);
    }

    public function update(UpdateCustomerProfileRequest $request): RedirectResponse
    {
        $customer = $request->user();
        $data = $request->validated();

        $customer->name = $data['name'];
        $customer->birth_day = $data['birth_day'] ?? null;
        $customer->birth_month = $data['birth_month'] ?? null;
        $customer->anniversary_day = $data['anniversary_day'] ?? null;
        $customer->anniversary_month = $data['anniversary_month'] ?? null;
        $customer->gender = $data['gender'] ?? null;
        $customer->save();

        return redirect()->route('account.profile.edit')->with('status', __('Profile updated.'));
    }

    public function destroy(Request $request, CustomerDeletionService $deletionService): RedirectResponse
    {
        $customer = $request->user();

        $deletionService->softDeleteCustomer($customer, User::DELETION_REASON_CUSTOMER);

        return redirect()->route('home')->with('status', __('Your account has been deleted.'));
    }
}
