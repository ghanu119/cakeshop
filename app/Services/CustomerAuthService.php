<?php

namespace App\Services;

use App\Mail\CustomerLoginOtp;
use App\Messaging\Contracts\MessagingGateway;
use App\Messaging\Exceptions\MessageDeliveryException;
use App\Models\CustomerAccountEvent;
use App\Models\LoginOtp;
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

    public const SESSION_VERIFIED_PHONE = 'customer_otp_verified_phone';

    public const OTP_EXPIRY_MINUTES = 10;

    public const SEND_OTP_EMAIL_MAX = 5;

    public const SEND_OTP_EMAIL_DECAY_SECONDS = 900;

    public const SEND_OTP_IP_MAX = 20;

    public const SEND_OTP_IP_DECAY_SECONDS = 900;

    public function __construct(
        private OrderLinkingService $orderLinkingService,
        private Request $request,
        private MessagingGateway $messaging,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Email OTP (existing public API — behavior preserved)
    |--------------------------------------------------------------------------
    */

    public function sendOtp(string $email): void
    {
        $email = strtolower(trim($email));

        $this->ensureCanSendOtp(LoginOtp::CHANNEL_EMAIL, $email);

        $code = $this->issueOtp(LoginOtp::CHANNEL_EMAIL, $email);

        Mail::to($email)->send(new CustomerLoginOtp($code, self::OTP_EXPIRY_MINUTES));
    }

    public function verifyOtp(string $email, string $code): void
    {
        $email = strtolower(trim($email));

        $this->confirmOtp(LoginOtp::CHANNEL_EMAIL, $email, $code, 'code');

        session([self::SESSION_VERIFIED_EMAIL => $email]);
    }

    public function verifiedEmail(): ?string
    {
        $email = session(self::SESSION_VERIFIED_EMAIL);

        return is_string($email) && $email !== '' ? $email : null;
    }

    /*
    |--------------------------------------------------------------------------
    | WhatsApp OTP
    |--------------------------------------------------------------------------
    */

    /**
     * Send a WhatsApp OTP to the given number.
     *
     * @return string the normalized phone the code was keyed to.
     *
     * @throws ValidationException with a human-readable message on failure.
     */
    public function sendWhatsAppOtp(string $phone): string
    {
        $normalized = $this->normalizePhoneOrFail($phone);

        $this->ensureCanSendOtp(LoginOtp::CHANNEL_WHATSAPP, $normalized);

        $code = $this->issueOtp(LoginOtp::CHANNEL_WHATSAPP, $normalized);

        try {
            $this->messaging->sendOtp($normalized, $code);
        } catch (MessageDeliveryException $e) {
            // Invalidate the just-issued code so the user can retry cleanly.
            LoginOtp::query()
                ->where('channel', LoginOtp::CHANNEL_WHATSAPP)
                ->where('destination', $normalized)
                ->whereNull('consumed_at')
                ->update(['consumed_at' => now()]);

            throw ValidationException::withMessages([
                'phone' => [$this->whatsappFailureMessage($e)],
            ]);
        }

        return $normalized;
    }

    public function verifyWhatsAppOtp(string $phone, string $code): string
    {
        $normalized = $this->normalizePhoneOrFail($phone);

        $this->confirmOtp(LoginOtp::CHANNEL_WHATSAPP, $normalized, $code, 'code');

        session([self::SESSION_VERIFIED_PHONE => $normalized]);

        return $normalized;
    }

    public function verifiedPhone(): ?string
    {
        $phone = session(self::SESSION_VERIFIED_PHONE);

        return is_string($phone) && $phone !== '' ? $phone : null;
    }

    public function whatsappEnabled(): bool
    {
        return (bool) config('services.whatsapp.enabled', false) && $this->messaging->isEnabled();
    }

    /*
    |--------------------------------------------------------------------------
    | Customer lookups
    |--------------------------------------------------------------------------
    */

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
        $this->clearVerifiedSession();

        $this->orderLinkingService->linkGuestOrdersForUser($customer);
    }

    public function createCustomer(string $name, string $phone, ?string $email = null, bool $emailVerified = true): User
    {
        $email = $email !== null ? strtolower(trim($email)) : $this->verifiedEmail();
        $normalizedPhone = PhoneNormalizer::normalize($phone);

        $user = new User;
        $user->name = trim($name);
        $user->phone = $normalizedPhone;
        $user->email = $email;
        // Only stamp email as verified when it was actually confirmed via email OTP.
        $user->email_verified_at = ($email && $emailVerified) ? now() : null;
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

    /*
    |--------------------------------------------------------------------------
    | Verified-email resolution (existing)
    |--------------------------------------------------------------------------
    */

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

        throw ValidationException::withMessages([
            'phone' => [__('This phone number is already linked to a different account. Please sign in with the matching email, or contact the store.')],
        ]);
    }

    public function authenticateCustomerForVerifiedEmail(string $email, string $phone, string $name): User
    {
        $customer = $this->resolveCustomerForVerifiedEmail($email, $phone, $name);
        $this->loginCustomer($customer);

        return $customer;
    }

    /*
    |--------------------------------------------------------------------------
    | Verified-phone resolution (WhatsApp)
    |--------------------------------------------------------------------------
    */

    public function assertPhoneOtpVerifiedFor(string $phone): void
    {
        $normalized = PhoneNormalizer::normalize($phone);
        $verified = $this->verifiedPhone();

        if ($verified === null || $normalized === null || $verified !== $normalized) {
            throw ValidationException::withMessages([
                'guest_phone' => [__('Please verify your WhatsApp number with the code we sent before placing your order.')],
            ]);
        }
    }

    public function resolveCustomerForVerifiedPhone(string $phone, string $name, ?string $email = null): User
    {
        $normalized = PhoneNormalizer::normalize($phone);
        $verified = $this->verifiedPhone();

        if ($verified === null || $normalized === null || $verified !== $normalized) {
            throw ValidationException::withMessages([
                'phone' => [__('Your session has expired. Please verify your WhatsApp number again.')],
            ]);
        }

        $customer = $this->findCustomerByPhone($normalized);

        if ($customer === null) {
            $normalizedEmail = $email !== null && trim($email) !== '' ? strtolower(trim($email)) : null;

            if ($normalizedEmail !== null) {
                $byEmail = $this->findCustomerByEmail($normalizedEmail);

                if ($byEmail !== null) {
                    if ($byEmail->phone !== null && $byEmail->phone !== '') {
                        throw ValidationException::withMessages([
                            'email' => [__('This email is already linked to a different account. Please sign in with the matching phone number, or contact the store.')],
                        ]);
                    }

                    $customer = $this->claimEmailOnlyAccount($byEmail, $normalized, $name);
                    $this->markWhatsAppVerified($customer);

                    return $customer;
                }
            }

            // Email (if provided) was entered during a WhatsApp verification,
            // so it is NOT email-verified — only the phone/WhatsApp is.
            $customer = $this->createCustomer($name, $normalized, $normalizedEmail, emailVerified: false);
        }

        $this->markWhatsAppVerified($customer);

        return $customer;
    }

    public function claimEmailOnlyAccount(User $existing, string $phone, string $name): User
    {
        $verified = $this->verifiedPhone();

        if ($verified === null || $verified !== $phone) {
            throw ValidationException::withMessages([
                'phone' => [__('Your session has expired. Please start again.')],
            ]);
        }

        if ($existing->phone !== null && $existing->phone !== '') {
            throw ValidationException::withMessages([
                'email' => [__('This email is already linked to a different account. Please sign in with the matching phone number, or contact the store.')],
            ]);
        }

        $existing->phone = $phone;
        $existing->name = trim($name);
        $existing->save();

        CustomerAccountEvent::create([
            'user_id' => $existing->id,
            'event' => 'phone_claimed',
            'email' => $existing->email,
            'ip_address' => $this->request->ip(),
            'created_at' => now(),
        ]);

        return $existing;
    }

    /**
     * Guard used before an OTP is sent during guest checkout: if the typed
     * email and phone already resolve to two different existing accounts,
     * fail fast instead of sending a code that will only fail at verify time.
     */
    public function assertContactsNotConflicting(?string $email, ?string $phone): void
    {
        $normalizedEmail = $email !== null && trim($email) !== '' ? strtolower(trim($email)) : null;
        $normalizedPhone = $phone !== null && trim($phone) !== '' ? PhoneNormalizer::normalize($phone) : null;

        if ($normalizedEmail === null || $normalizedPhone === null) {
            return;
        }

        $byEmail = $this->findCustomerByEmail($normalizedEmail);
        $byPhone = $this->findCustomerByPhone($normalizedPhone);

        if ($byEmail !== null && $byPhone !== null && $byEmail->id !== $byPhone->id) {
            throw ValidationException::withMessages([
                'email' => [__('This email and phone number are linked to different accounts. Please double-check your details or sign in using just one of them.')],
            ]);
        }
    }

    public function authenticateCustomerForVerifiedPhone(string $phone, string $name, ?string $email = null): User
    {
        $customer = $this->resolveCustomerForVerifiedPhone($phone, $name, $email);
        $this->loginCustomer($customer);

        return $customer;
    }

    public function clearVerifiedSession(): void
    {
        session()->forget(self::SESSION_VERIFIED_EMAIL);
        session()->forget(self::SESSION_VERIFIED_PHONE);
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

    public function maskPhone(?string $phone): string
    {
        return PhoneNormalizer::mask($phone);
    }

    /*
    |--------------------------------------------------------------------------
    | Internals
    |--------------------------------------------------------------------------
    */

    private function issueOtp(string $channel, string $destination): string
    {
        LoginOtp::query()
            ->where('channel', $channel)
            ->where('destination', $destination)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->update(['consumed_at' => now()]);

        $code = (string) random_int(100000, 999999);

        LoginOtp::create([
            'channel' => $channel,
            'destination' => $destination,
            'code_hash' => Hash::make($code),
            'purpose' => LoginOtp::PURPOSE_LOGIN,
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'ip_address' => $this->request->ip(),
            'created_at' => now(),
        ]);

        return $code;
    }

    private function confirmOtp(string $channel, string $destination, string $code, string $field): void
    {
        $otp = LoginOtp::query()
            ->where('channel', $channel)
            ->where('destination', $destination)
            ->whereNull('consumed_at')
            ->orderByDesc('id')
            ->first();

        if (! $otp || $otp->isExpired()) {
            throw ValidationException::withMessages([
                $field => [__('This code has expired. Please request a new one.')],
            ]);
        }

        if ($otp->hasExceededAttempts()) {
            throw ValidationException::withMessages([
                $field => [__('Too many attempts. Please request a new code.')],
            ]);
        }

        if (! Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempts');

            throw ValidationException::withMessages([
                $field => [__('The code you entered is incorrect.')],
            ]);
        }

        $otp->forceFill(['consumed_at' => now()])->save();
    }

    private function markWhatsAppVerified(User $user): void
    {
        $user->phone_verified_at = now();
        $user->whatsapp_verified_at = now();
        $user->save();
    }

    private function normalizePhoneOrFail(string $phone): string
    {
        $normalized = PhoneNormalizer::normalize($phone);

        if ($normalized === null || strlen($normalized) < 8) {
            throw ValidationException::withMessages([
                'phone' => [__('Please enter a valid mobile number.')],
            ]);
        }

        return $normalized;
    }

    private function whatsappFailureMessage(MessageDeliveryException $e): string
    {
        return match ($e->reason) {
            MessageDeliveryException::REASON_INVALID_NUMBER,
            MessageDeliveryException::REASON_UNDELIVERABLE => __('We couldn\'t send a WhatsApp code to this number — it may not be on WhatsApp. Try another number or use email instead.'),
            MessageDeliveryException::REASON_DISABLED => __('WhatsApp sign-in is currently unavailable. Please use email instead.'),
            default => __('We couldn\'t send your WhatsApp code right now. Please try again or use email instead.'),
        };
    }

    private function ensureCanSendOtp(string $channel, string $destination): void
    {
        $destKey = 'customer-otp-send:'.$channel.':'.sha1($destination);
        $ipKey = 'customer-otp-send:ip:'.sha1((string) $this->request->ip());

        if (RateLimiter::tooManyAttempts($destKey, self::SEND_OTP_EMAIL_MAX)) {
            $this->throwSendOtpRateLimitException($destKey, $channel);
        }

        if (RateLimiter::tooManyAttempts($ipKey, self::SEND_OTP_IP_MAX)) {
            $this->throwSendOtpRateLimitException($ipKey, $channel);
        }

        RateLimiter::hit($destKey, self::SEND_OTP_EMAIL_DECAY_SECONDS);
        RateLimiter::hit($ipKey, self::SEND_OTP_IP_DECAY_SECONDS);
    }

    private function throwSendOtpRateLimitException(string $key, string $channel): void
    {
        $seconds = RateLimiter::availableIn($key);
        $minutes = max(1, (int) ceil($seconds / 60));
        $field = $channel === LoginOtp::CHANNEL_WHATSAPP ? 'phone' : 'email';

        throw ValidationException::withMessages([
            $field => [__('Too many sign-in code requests. Please wait :minutes minute(s) and try again.', ['minutes' => $minutes])],
        ]);
    }
}
