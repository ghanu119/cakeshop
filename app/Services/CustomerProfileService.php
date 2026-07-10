<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class CustomerProfileService
{
    public function __construct(
        private CustomerAuthService $customerAuthService,
    ) {}

    public function sendEmailVerificationOtp(User $customer, string $email): void
    {
        $email = strtolower(trim($email));

        if ($customer->hasEmail() && strtolower($customer->email) === $email) {
            throw ValidationException::withMessages([
                'email' => [__('This is already your email address.')],
            ]);
        }

        $this->assertEmailAvailable($customer, $email);

        $this->customerAuthService->sendOtp($email);
    }

    public function verifyAndUpdateEmail(User $customer, string $email, string $code): void
    {
        $email = strtolower(trim($email));

        $this->customerAuthService->verifyOtp($email, $code);

        $verified = $this->customerAuthService->verifiedEmail();

        if ($verified === null || $verified !== $email) {
            throw ValidationException::withMessages([
                'code' => [__('Your session has expired. Please request a new code.')],
            ]);
        }

        $this->assertEmailAvailable($customer, $email);

        $wasPhoneOnly = $customer->isPhoneOnly();

        $customer->email = $email;
        $customer->email_verified_at = now();

        if ($wasPhoneOnly) {
            $customer->email_claimed_at = now();
        }

        $customer->save();

        $this->customerAuthService->clearVerifiedSession();
    }

    private function assertEmailAvailable(User $customer, string $email): void
    {
        $existingUser = User::where('email', $email)->first();

        if ($existingUser?->isStaff()) {
            throw ValidationException::withMessages([
                'email' => [__('This email cannot be used for a customer account.')],
            ]);
        }

        if ($existingUser !== null && $existingUser->id !== $customer->id) {
            throw ValidationException::withMessages([
                'email' => [__('This email is already linked to another account.')],
            ]);
        }
    }
}
