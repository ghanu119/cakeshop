<?php

namespace App\Livewire\Account;

use App\Services\CustomerAuthService;
use App\Services\CustomerProfileService;
use App\Support\AuthGuards;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ProfileEmailVerification extends Component
{
    public string $currentEmail = '';

    public string $email = '';

    public string $code = '';

    public string $step = 'edit';

    public ?string $statusMessage = null;

    public function mount(): void
    {
        $customer = auth(AuthGuards::CUSTOMER)->user();
        $this->currentEmail = strtolower(trim($customer->email ?? ''));
        $this->email = $this->currentEmail;
    }

    public function sendOtp(CustomerProfileService $profileService): void
    {
        $this->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($this->email));

        if ($email === $this->currentEmail) {
            throw ValidationException::withMessages([
                'email' => [__('Enter a different email address to update.')],
            ]);
        }

        $profileService->sendEmailVerificationOtp(auth(AuthGuards::CUSTOMER)->user(), $email);

        $this->email = $email;
        $this->step = 'otp';
        $this->code = '';
        $this->statusMessage = __('We sent a verification code to your email.');
        $this->resetValidation();
    }

    public function verifyOtp(CustomerProfileService $profileService): void
    {
        $this->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $customer = auth(AuthGuards::CUSTOMER)->user();
        $email = strtolower(trim($this->email));

        $profileService->verifyAndUpdateEmail($customer, $email, $this->code);

        $this->currentEmail = $email;
        $this->step = 'edit';
        $this->code = '';
        $this->statusMessage = __('Your email has been updated.');
    }

    public function resendOtp(CustomerProfileService $profileService): void
    {
        $this->sendOtp($profileService);
    }

    public function cancelOtp(CustomerAuthService $customerAuthService): void
    {
        $this->step = 'edit';
        $this->email = $this->currentEmail;
        $this->code = '';
        $this->statusMessage = null;
        $customerAuthService->clearVerifiedSession();
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.account.profile-email-verification', [
            'maskedEmail' => app(CustomerAuthService::class)->maskEmail($this->email),
        ]);
    }
}
