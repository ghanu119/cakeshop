<?php

namespace Tests\Feature;

use App\Livewire\Account\AuthModal;
use App\Mail\CustomerLoginOtp;
use App\Models\User;
use App\Models\User\RegisteredVia;
use App\Services\CustomerAuthService;
use App\Support\AuthGuards;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    private function sendAndCaptureOtp(string $email): string
    {
        Mail::fake();

        Livewire::test(AuthModal::class)
            ->set('email', $email)
            ->call('sendOtp');

        $code = null;
        Mail::assertSent(CustomerLoginOtp::class, function (CustomerLoginOtp $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        return $code;
    }

    public function test_new_customer_can_register_via_otp_modal(): void
    {
        $email = 'newcustomer@example.com';
        $code = $this->sendAndCaptureOtp($email);

        Livewire::test(AuthModal::class)
            ->set('email', $email)
            ->set('code', $code)
            ->call('verifyOtp')
            ->assertSet('step', 'profile')
            ->set('name', 'New Customer')
            ->set('phone', '9876543210')
            ->call('completeProfile');

        $this->assertAuthenticated(AuthGuards::CUSTOMER);
        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->isCustomer());
        $this->assertSame('9876543210', $user->phone);
    }

    public function test_existing_customer_logs_in_without_registration(): void
    {
        $customer = User::factory()->customer()->create([
            'email' => 'existing@example.com',
            'phone' => '9123456789',
        ]);

        $code = $this->sendAndCaptureOtp($customer->email);

        Livewire::test(AuthModal::class)
            ->set('email', $customer->email)
            ->set('code', $code)
            ->call('verifyOtp')
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($customer, AuthGuards::CUSTOMER);
    }

    public function test_phone_only_customer_claims_account_on_register(): void
    {
        $existing = User::factory()->customer()->create([
            'name' => 'Walk In',
            'email' => null,
            'phone' => '9111222333',
            'registered_via' => RegisteredVia::ADMIN_CREATED,
        ]);

        $email = 'claimed@example.com';
        $code = $this->sendAndCaptureOtp($email);

        Livewire::test(AuthModal::class)
            ->set('email', $email)
            ->set('code', $code)
            ->call('verifyOtp')
            ->set('name', 'Walk In')
            ->set('phone', '9111222333')
            ->call('completeProfile');

        $existing->refresh();
        $this->assertSame($email, $existing->email);
        $this->assertNotNull($existing->email_claimed_at);
        $this->assertAuthenticatedAs($existing, AuthGuards::CUSTOMER);
    }

    public function test_staff_cannot_use_customer_otp_login(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $admin->assignRole('Admin');

        Livewire::test(AuthModal::class)
            ->set('email', $admin->email)
            ->call('sendOtp')
            ->assertHasErrors('email');

        $this->assertGuest(AuthGuards::CUSTOMER);
        $this->assertGuest(AuthGuards::STAFF);
    }

    public function test_invalid_email_is_rejected_on_send_otp(): void
    {
        Mail::fake();

        Livewire::test(AuthModal::class)
            ->set('channel', 'email')
            ->set('email', 'not-an-email')
            ->call('sendOtp')
            ->assertHasErrors('email');

        Mail::assertNothingSent();
    }

    public function test_email_sign_up_profile_rejects_invalid_phone(): void
    {
        Mail::fake();
        $email = 'profilephone@example.com';
        $code = $this->sendAndCaptureOtp($email);

        Livewire::test(AuthModal::class)
            ->set('channel', 'email')
            ->set('email', $email)
            ->set('code', $code)
            ->call('verifyOtp')
            ->assertSet('step', 'profile')
            ->set('name', 'Test User')
            ->set('phone', '12345')
            ->call('completeProfile')
            ->assertHasErrors('phone');

        $this->assertGuest(AuthGuards::CUSTOMER);
    }

    public function test_legacy_login_route_redirects_with_auth_flag(): void
    {
        $this->get(route('account.login'))
            ->assertRedirect();
    }

    public function test_send_otp_is_rate_limited_per_email_after_repeated_requests(): void
    {
        Mail::fake();

        $email = 'ratelimit@example.com';

        for ($i = 0; $i < CustomerAuthService::SEND_OTP_EMAIL_MAX; $i++) {
            Livewire::test(AuthModal::class)
                ->set('email', $email)
                ->call('sendOtp')
                ->assertHasNoErrors();
        }

        Livewire::test(AuthModal::class)
            ->set('email', $email)
            ->call('sendOtp')
            ->assertHasErrors('email');
    }

    public function test_auth_modal_resets_when_closed(): void
    {
        Livewire::test(AuthModal::class)
            ->set('email', 'user@example.com')
            ->set('step', 'otp')
            ->set('code', '123456')
            ->dispatch('close-modal', 'customer-auth-modal')
            ->assertSet('step', 'contact')
            ->assertSet('email', '')
            ->assertSet('code', '');
    }

    public function test_finish_auth_redirects_to_referer_without_auth_query(): void
    {
        Mail::fake();

        $customer = User::factory()->customer()->create([
            'email' => 'referer@example.com',
            'phone' => '9000000001',
        ]);

        $code = $this->sendAndCaptureOtp($customer->email);

        Livewire::withHeaders(['Referer' => route('home').'?auth=1'])
            ->test(AuthModal::class)
            ->set('email', $customer->email)
            ->set('code', $code)
            ->call('verifyOtp')
            ->assertRedirect(route('home'));
    }
}
