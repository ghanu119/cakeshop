<?php

namespace App\Services;

use App\Mail\CustomerLoginOtp;
use App\Models\CustomerAccountEvent;
use App\Models\EmailLoginOtp;
use App\Models\User;
use App\Models\User\RegisteredVia;
use App\Support\AuthGuards;
use App\Support\PhoneNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerAuthService
{
    public const SESSION_VERIFIED_EMAIL = 'customer_otp_verified_email';

    public const OTP_EXPIRY_MINUTES = 10;

    public const SEND_OTP_EMAIL_MAX = 5;

    public const SEND_OTP_EMAIL_DECAY_SECONDS = 900;

    public const SEND_OTP_IP_MAX = 20;

    public const SEND_OTP_IP_DECAY_SECONDS = 900;

    public function __construct(
        private OrderLinkingService $orderLinkingService,
        private Request $request
    ) {}

    public function sendOtp(string $email): void
    {
        $email = strtolower(trim($email));

        $this->ensureCanSendOtp($email);

        EmailLoginOtp::query()
            ->where('email', $email)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->update(['consumed_at' => now()]);

        $code = (string) random_int(100000, 999999);

        EmailLoginOtp::create([
            'email' => $email,
            'code_hash' => Hash::make($code),
            'purpose' => EmailLoginOtp::PURPOSE_LOGIN,
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'ip_address' => $this->request->ip(),
            'created_at' => now(),
        ]);

        Mail::to($email)->send(new CustomerLoginOtp($code, self::OTP_EXPIRY_MINUTES));
    }

    public function verifyOtp(string $email, string $code): void
    {
        $email = strtolower(trim($email));

        $otp = EmailLoginOtp::query()
            ->where('email', $email)
            ->whereNull('consumed_at')
            ->orderByDesc('id')
            ->first();

        if (! $otp || $otp->isExpired()) {
            throw $this->otpValidationException($email, 'code', __('This code has expired. Please request a new one.'));
        }

        if ($otp->hasExceededAttempts()) {
            throw $this->otpValidationException($email, 'code', __('Too many attempts. Please request a new code.'));
        }

        if (! Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempts');

            throw $this->otpValidationException($email, 'code', __('The code you entered is incorrect.'));
        }

        $otp->forceFill(['consumed_at' => now()])->save();
        session([self::SESSION_VERIFIED_EMAIL => $email]);
    }

    public function verifiedEmail(): ?string
    {
        $email = session(self::SESSION_VERIFIED_EMAIL);

        return is_string($email) && $email !== '' ? $email : null;
    }

    public function findCustomerByEmail(string $email): ?User
    {
        return User::customers()->where('email', strtolower(trim($email)))->first();
    }

    public function findPhoneOnlyCustomer(string $phone): ?User
    {
        $normalized = PhoneNormalizer::normalize($phone);

        if ($normalized === null) {
            return null;
        }

        return User::customers()
            ->whereNull('email')
            ->where('phone', $normalized)
            ->first();
    }

    public function findCustomerByPhone(string $phone): ?User
    {
        $normalized = PhoneNormalizer::normalize($phone);

        if ($normalized === null) {
            return null;
        }

        return User::customers()->where('phone', $normalized)->first();
    }

    public function loginCustomer(User $customer): void
    {
        if (! $customer->isCustomer()) {
            throw ValidationException::withMessages([
                'email' => [__('This account cannot sign in here.')],
            ]);
        }

        if ($customer->isStaff()) {
            throw ValidationException::withMessages([
                'email' => [__('Please use the staff login page.')],
            ]);
        }

        Auth::guard(AuthGuards::CUSTOMER)->login($customer);
        session()->regenerate();
        session()->forget(self::SESSION_VERIFIED_EMAIL);

        $this->orderLinkingService->linkGuestOrdersForUser($customer);
    }

    public function createCustomer(string $name, string $phone, ?string $email = null): User
    {
        $email = $email !== null ? strtolower(trim($email)) : $this->verifiedEmail();
        $normalizedPhone = PhoneNormalizer::normalize($phone);

        $user = new User;
        $user->name = trim($name);
        $user->phone = $normalizedPhone;
        $user->email = $email;
        $user->email_verified_at = $email ? now() : null;
        $user->registered_via = RegisteredVia::FRONT_OTP;
        $user->password = null;
        $user->save();
        $user->assignRole('Customer');

        CustomerAccountEvent::create([
            'user_id' => $user->id,
            'event' => 'account_created',
            'email' => $email,
            'ip_address' => $this->request->ip(),
            'created_at' => now(),
        ]);

        return $user;
    }

    public function claimPhoneOnlyAccount(User $existing, string $name): User
    {
        $email = $this->verifiedEmail();

        if ($email === null) {
            throw ValidationException::withMessages([
                'email' => [__('Your session has expired. Please start again.')],
            ]);
        }

        if ($existing->hasEmail()) {
            throw ValidationException::withMessages([
                'phone' => [__('This phone number is already linked to an account. Please sign in with your email or contact the store.')],
            ]);
        }

        $existing->email = $email;
        $existing->email_verified_at = now();
        $existing->email_claimed_at = now();
        $existing->name = trim($name);
        $existing->save();

        CustomerAccountEvent::create([
            'user_id' => $existing->id,
            'event' => 'account_claimed',
            'email' => $email,
            'ip_address' => $this->request->ip(),
            'created_at' => now(),
        ]);

        return $existing;
    }

    public function assertOtpVerifiedFor(string $email): void
    {
        $verified = $this->verifiedEmail();

        if ($verified === null || strtolower(trim($email)) !== $verified) {
            throw ValidationException::withMessages([
                'guest_email' => [__('Please verify your email with the code we sent before placing your order.')],
            ]);
        }
    }

    public function resolveCustomerForVerifiedEmail(string $email, string $phone, string $name): User
    {
        $email = strtolower(trim($email));
        $verified = $this->verifiedEmail();

        if ($verified === null || $verified !== $email) {
            throw ValidationException::withMessages([
                'email' => [__('Your session has expired. Please verify your email again.')],
            ]);
        }

        $byEmail = $this->findCustomerByEmail($email);

        if ($byEmail !== null) {
            return $byEmail;
        }

        $byPhone = $this->findCustomerByPhone($phone);

        if ($byPhone === null) {
            return $this->createCustomer($name, $phone, $email);
        }

        if (! $byPhone->hasEmail()) {
            return $this->claimPhoneOnlyAccount($byPhone, $name);
        }

        return $byPhone;
    }

    public function authenticateCustomerForVerifiedEmail(string $email, string $phone, string $name): User
    {
        $customer = $this->resolveCustomerForVerifiedEmail($email, $phone, $name);
        $this->loginCustomer($customer);

        return $customer;
    }

    public function clearVerifiedSession(): void
    {
        session()->forget(self::SESSION_VERIFIED_EMAIL);
    }

    public function maskEmail(?string $email): string
    {
        if ($email === null || ! str_contains($email, '@')) {
            return '••••';
        }

        [$local, $domain] = explode('@', $email, 2);
        $maskedLocal = Str::substr($local, 0, 1).str_repeat('•', max(1, strlen($local) - 1));

        return $maskedLocal.'@'.$domain;
    }

    private function otpValidationException(string $email, string $field, string $message): ValidationException
    {
        return ValidationException::withMessages([
            $field => [$message],
        ]);
    }

    private function ensureCanSendOtp(string $email): void
    {
        $emailKey = 'customer-otp-send:email:'.sha1($email);
        $ipKey = 'customer-otp-send:ip:'.sha1((string) $this->request->ip());

        if (RateLimiter::tooManyAttempts($emailKey, self::SEND_OTP_EMAIL_MAX)) {
            $this->throwSendOtpRateLimitException($emailKey, $email);
        }

        if (RateLimiter::tooManyAttempts($ipKey, self::SEND_OTP_IP_MAX)) {
            $this->throwSendOtpRateLimitException($ipKey, $email);
        }

        RateLimiter::hit($emailKey, self::SEND_OTP_EMAIL_DECAY_SECONDS);
        RateLimiter::hit($ipKey, self::SEND_OTP_IP_DECAY_SECONDS);
    }

    private function throwSendOtpRateLimitException(string $key, string $email): void
    {
        $seconds = RateLimiter::availableIn($key);
        $minutes = max(1, (int) ceil($seconds / 60));

        throw ValidationException::withMessages([
            'email' => [__('Too many sign-in code requests. Please wait :minutes minute(s) and try again.', ['minutes' => $minutes])],
        ]);
    }
}
