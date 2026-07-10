<?php

namespace App\Livewire\Account;

use App\Models\LoginOtp;
use App\Models\User;
use App\Rules\IndianMobileNumber;
use App\Services\CustomerAuthService;
use App\Support\AuthGuards;
use App\Support\PhoneNormalizer;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class AuthModal extends Component
{
    public string $step = 'contact';

    public string $channel = LoginOtp::CHANNEL_EMAIL;

    public string $email = '';

    public string $phone = '';

    public string $code = '';

    public string $name = '';

    public ?string $statusMessage = null;

    public ?array $phoneOnlyPreview = null;

    public function mount(): void
    {
        $this->channel = $this->defaultChannel();
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

    public function switchChannel(string $channel): void
    {
        if ($channel === LoginOtp::CHANNEL_WHATSAPP && ! $this->whatsappEnabled()) {
            $channel = LoginOtp::CHANNEL_EMAIL;
        }

        if (! in_array($channel, [LoginOtp::CHANNEL_EMAIL, LoginOtp::CHANNEL_WHATSAPP], true)) {
            return;
        }

        $this->channel = $channel;
        $this->step = 'contact';
        $this->code = '';
        $this->statusMessage = null;
        $this->resetValidation();
        app(CustomerAuthService::class)->clearVerifiedSession();
    }

    public function sendOtp(CustomerAuthService $customerAuthService): void
    {
        if ($this->channel === LoginOtp::CHANNEL_WHATSAPP) {
            $this->sendWhatsAppOtp($customerAuthService);

            return;
        }

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

    private function sendWhatsAppOtp(CustomerAuthService $customerAuthService): void
    {
        $this->validate([
            'phone' => ['required', 'string', new IndianMobileNumber],
        ]);

        // Throws ValidationException (keyed to 'phone') on delivery failure,
        // keeping the user on the contact step with a clear message.
        $this->phone = $customerAuthService->sendWhatsAppOtp($this->phone);
        $this->step = 'otp';
        $this->code = '';
        $this->statusMessage = __('We sent a sign-in code to your WhatsApp.');
    }

    public function verifyOtp(CustomerAuthService $customerAuthService): void
    {
        $this->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        if ($this->channel === LoginOtp::CHANNEL_WHATSAPP) {
            $this->verifyWhatsAppOtp($customerAuthService);

            return;
        }

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

    private function verifyWhatsAppOtp(CustomerAuthService $customerAuthService): void
    {
        $phone = $customerAuthService->verifyWhatsAppOtp($this->phone, $this->code);

        $customer = $customerAuthService->findCustomerByPhone($phone);

        if ($customer !== null && $customer->hasEmail()) {
            $customerAuthService->authenticateCustomerForVerifiedPhone($phone, $customer->name);
            $this->finishAuth();

            return;
        }

        if ($customer !== null) {
            // Phone-only account: just needs a login (name already known).
            $customerAuthService->authenticateCustomerForVerifiedPhone($phone, $customer->name);
            $this->finishAuth();

            return;
        }

        $this->step = 'profile';
        $this->statusMessage = __('Almost done — tell us your name and email.');
    }

    public function resendOtp(CustomerAuthService $customerAuthService): void
    {
        $this->sendOtp($customerAuthService);
    }

    public function updatedPhone(): void
    {
        $this->phoneOnlyPreview = null;

        if ($this->channel !== LoginOtp::CHANNEL_EMAIL || $this->step !== 'profile') {
            return;
        }

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
        if ($this->channel === LoginOtp::CHANNEL_WHATSAPP) {
            $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
            ]);

            $email = trim($this->email) !== '' ? strtolower(trim($this->email)) : null;

            $customerAuthService->authenticateCustomerForVerifiedPhone(
                $this->phone,
                $this->name,
                $email,
            );

            $this->finishAuth();

            return;
        }

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', new IndianMobileNumber],
        ]);

        $customerAuthService->authenticateCustomerForVerifiedEmail(
            $this->email,
            $this->phone,
            $this->name,
        );

        $this->finishAuth();
    }

    public function goBackToContact(): void
    {
        $this->step = 'contact';
        $this->code = '';
        $this->statusMessage = null;
        $this->resetValidation();
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
    }

    #[On('close-modal')]
    public function onModalClosed(string $name): void
    {
        if ($name === 'customer-auth-modal') {
            $this->resetForm();
        }
    }

    public function whatsappEnabled(): bool
    {
        return app(CustomerAuthService::class)->whatsappEnabled();
    }

    private function defaultChannel(): string
    {
        return $this->whatsappEnabled() ? LoginOtp::CHANNEL_WHATSAPP : LoginOtp::CHANNEL_EMAIL;
    }

    private function finishAuth(): void
    {
        $intended = session()->pull('url.intended');

        $this->redirect($intended ?? $this->resolvePostLoginUrl(), navigate: false);
    }

    private function resolvePostLoginUrl(): string
    {
        $referer = request()->headers->get('referer');

        if ($referer !== null && ! str_contains($referer, '/livewire/')) {
            return $this->stripAuthQueryParam($referer);
        }

        return route('home');
    }

    private function stripAuthQueryParam(string $url): string
    {
        $parts = parse_url($url);

        if (! isset($parts['query'])) {
            return $url;
        }

        parse_str($parts['query'], $query);
        unset($query['auth']);

        $base = ($parts['scheme'] ?? 'https').'://'
            .($parts['host'] ?? '')
            .(isset($parts['port']) ? ':'.$parts['port'] : '')
            .($parts['path'] ?? '');

        if ($query === []) {
            return $base;
        }

        return $base.'?'.http_build_query($query);
    }

    private function resetForm(): void
    {
        $this->step = 'contact';
        $this->channel = $this->defaultChannel();
        $this->email = '';
        $this->phone = '';
        $this->code = '';
        $this->name = '';
        $this->statusMessage = null;
        $this->phoneOnlyPreview = null;
        $this->resetValidation();
    }

    public function render(CustomerAuthService $customerAuthService)
    {
        return view('livewire.account.auth-modal', [
            'maskedEmail' => $customerAuthService->maskEmail($this->email ?: null),
            'maskedPhone' => $customerAuthService->maskPhone($this->phone ?: null),
            'whatsappEnabled' => $this->whatsappEnabled(),
        ]);
    }
}
