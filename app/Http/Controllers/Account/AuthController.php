<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\RegisterCustomerRequest;
use App\Http\Requests\Account\SendOtpRequest;
use App\Http\Requests\Account\UpdateCustomerProfileRequest;
use App\Http\Requests\Account\VerifyOtpRequest;
use App\Models\User;
use App\Services\CustomerAuthService;
use App\Services\CustomerDeletionService;
use App\Support\PhoneNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private CustomerAuthService $customerAuthService
    ) {}

    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->user()?->isCustomer()) {
            return redirect()->route('account.dashboard');
        }

        return view('account.auth.login', [
            'intended' => $request->query('intended'),
        ]);
    }

    public function sendOtp(SendOtpRequest $request): RedirectResponse
    {
        $email = $request->validated('email');

        if ($request->filled('intended')) {
            session(['url.intended' => $request->input('intended')]);
        }

        $existingUser = User::where('email', $email)->first();
        if ($existingUser?->isStaff()) {
            return back()->withErrors([
                'email' => __('Please use the staff login page.'),
            ])->withInput();
        }

        $this->customerAuthService->sendOtp($email);

        return redirect()
            ->route('account.verify-otp', ['email' => $email])
            ->with('status', __('We sent a sign-in code to your email.'));
    }

    public function showVerifyOtp(Request $request): View|RedirectResponse
    {
        $email = $request->query('email');

        if (! is_string($email) || $email === '') {
            return redirect()->route('account.login');
        }

        return view('account.auth.verify-otp', [
            'email' => strtolower($email),
            'maskedEmail' => $this->customerAuthService->maskEmail($email),
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request): RedirectResponse
    {
        $email = $request->validated('email');
        $this->customerAuthService->verifyOtp($email, $request->validated('code'));

        $customer = $this->customerAuthService->findCustomerByEmail($email);

        if ($customer) {
            $this->customerAuthService->loginCustomer($customer);

            return redirect()->intended(route('account.dashboard'));
        }

        return redirect()->route('account.register');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (! $this->customerAuthService->verifiedEmail()) {
            return redirect()->route('account.login');
        }

        return view('account.auth.register', [
            'verifiedEmail' => $this->customerAuthService->verifiedEmail(),
            'maskedEmail' => $this->customerAuthService->maskEmail($this->customerAuthService->verifiedEmail()),
        ]);
    }

    public function checkPhone(Request $request): \Illuminate\Http\JsonResponse
    {
        if (! $this->customerAuthService->verifiedEmail()) {
            return response()->json(['match' => null], 403);
        }

        $phoneOnly = $this->customerAuthService->findPhoneOnlyCustomer((string) $request->query('phone', ''));

        if (! $phoneOnly) {
            return response()->json(['match' => null]);
        }

        return response()->json([
            'match' => [
                'name' => $phoneOnly->name,
                'phone_masked' => PhoneNormalizer::mask($phoneOnly->phone),
            ],
        ]);
    }

    public function register(RegisterCustomerRequest $request): RedirectResponse
    {
        $name = $request->validated('name');
        $phone = $request->validated('phone');

        $existingByPhone = $this->customerAuthService->findCustomerByPhone($phone);

        if ($existingByPhone) {
            if ($existingByPhone->hasEmail()) {
                return back()->withErrors([
                    'phone' => __('This phone number is already linked to an account. Please sign in with your email or contact the store.'),
                ])->withInput();
            }

            if ($existingByPhone->isStaff()) {
                return back()->withErrors([
                    'phone' => __('This phone number cannot be used for a customer account.'),
                ])->withInput();
            }

            $customer = $this->customerAuthService->claimPhoneOnlyAccount($existingByPhone, $name);
        } else {
            $conflict = $this->customerAuthService->findCustomerByPhone($phone);
            if ($conflict) {
                return back()->withErrors([
                    'phone' => __('This phone number is already in use.'),
                ])->withInput();
            }

            $customer = $this->customerAuthService->createCustomer($name, $phone);
        }

        $this->customerAuthService->loginCustomer($customer);

        return redirect()->intended(route('account.dashboard'))
            ->with('status', __('Welcome! Your account is ready.'));
    }

    public function logout(Request $request): RedirectResponse
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
