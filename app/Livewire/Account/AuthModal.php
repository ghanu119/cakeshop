<?php

namespace App\Livewire\Account;

use App\Models\User;
use App\Services\CustomerAuthService;
use App\Support\PhoneNormalizer;
use App\Support\AuthGuards;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class AuthModal extends Component
{
    public string $step = 'email';

    public string $email = '';

    public string $code = '';

    public string $name = '';

    public string $phone = '';

    public ?string $statusMessage = null;

    public ?array $phoneOnlyPreview = null;

    public function mount(): void
    {
        if (auth(AuthGuards::CUSTOMER)->user()?->isCustomer()) {
            $this->step = 'email';
        }
    }

    #[On('open-auth-modal')]
    public function openModal(?string $intended = null): void
    {
        if ($intended) {
            session(['url.intended' => $intended]);
        }

        $this->resetForm();
        $this->dispatch('open-modal', 'customer-auth-modal');
    }

    public function sendOtp(CustomerAuthService $customerAuthService): void
    {
        $this->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($this->email));

        $existingUser = User::where('email', $email)->first();
        if ($existingUser?->isStaff()) {
            throw ValidationException::withMessages([
                'email' => [__('Please use the staff login page.')],
            ]);
        }

        $customerAuthService->sendOtp($email);
        $this->email = $email;
        $this->step = 'otp';
        $this->code = '';
        $this->statusMessage = __('We sent a sign-in code to your email.');
    }

    public function verifyOtp(CustomerAuthService $customerAuthService): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $email = strtolower(trim($this->email));
        $customerAuthService->verifyOtp($email, $this->code);

        $customer = $customerAuthService->findCustomerByEmail($email);

        if ($customer !== null) {
            $customerAuthService->loginCustomer($customer);
            $this->finishAuth();

            return;
        }

        $this->step = 'profile';
        $this->statusMessage = __('Almost done — tell us your name and phone number.');
    }

    public function resendOtp(CustomerAuthService $customerAuthService): void
    {
        $this->sendOtp($customerAuthService);
    }

    public function updatedPhone(): void
    {
        $this->phoneOnlyPreview = null;

        if (strlen(trim($this->phone)) < 6) {
            return;
        }

        $phoneOnly = app(CustomerAuthService::class)->findPhoneOnlyCustomer($this->phone);

        if ($phoneOnly === null) {
            return;
        }

        $this->phoneOnlyPreview = [
            'name' => $phoneOnly->name,
            'phone_masked' => PhoneNormalizer::mask($phoneOnly->phone),
        ];

        if (blank($this->name)) {
            $this->name = $phoneOnly->name;
        }
    }

    public function completeProfile(CustomerAuthService $customerAuthService): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
        ]);

        $customerAuthService->authenticateCustomerForVerifiedEmail(
            $this->email,
            $this->phone,
            $this->name,
        );

        $this->finishAuth();
    }

    public function goBackToEmail(): void
    {
        $this->step = 'email';
        $this->code = '';
        $this->statusMessage = null;
        app(CustomerAuthService::class)->clearVerifiedSession();
    }

    public function goBackToOtp(): void
    {
        $this->step = 'otp';
        $this->statusMessage = null;
    }

    public function close(): void
    {
        $this->dispatch('close-modal', 'customer-auth-modal');
        $this->resetForm();
    }

    private function finishAuth(): void
    {
        $intended = session()->pull('url.intended');

        $this->dispatch('close-modal', 'customer-auth-modal');
        $this->resetForm();

        if ($intended) {
            $this->redirect($intended, navigate: false);

            return;
        }

        $this->js('window.location.reload()');
    }

    private function resetForm(): void
    {
        $this->step = 'email';
        $this->email = '';
        $this->code = '';
        $this->name = '';
        $this->phone = '';
        $this->statusMessage = null;
        $this->phoneOnlyPreview = null;
        $this->resetValidation();
    }

    public function render(CustomerAuthService $customerAuthService)
    {
        return view('livewire.account.auth-modal', [
            'maskedEmail' => $customerAuthService->maskEmail($this->email ?: null),
        ]);
    }
}
