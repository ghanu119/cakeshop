<?php

namespace Tests\Feature;

use App\Mail\CustomerLoginOtp;
use App\Models\EmailLoginOtp;
use App\Models\Order;
use App\Models\User;
use App\Models\User\RegisteredVia;
use App\Services\CustomerAuthService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
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

        $this->post(route('account.login.send-otp'), ['email' => $email]);

        $code = null;
        Mail::assertSent(CustomerLoginOtp::class, function (CustomerLoginOtp $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        return $code;
    }

    public function test_new_customer_can_register_via_otp(): void
    {
        $email = 'newcustomer@example.com';
        $code = $this->sendAndCaptureOtp($email);

        $this->post(route('account.verify-otp.submit'), [
            'email' => $email,
            'code' => $code,
        ])->assertRedirect(route('account.register'));

        $this->post(route('account.register.submit'), [
            'name' => 'New Customer',
            'phone' => '9876543210',
        ])->assertRedirect(route('account.dashboard'));

        $this->assertAuthenticated();
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

        $this->post(route('account.verify-otp.submit'), [
            'email' => $customer->email,
            'code' => $code,
        ])->assertRedirect(route('account.dashboard'));

        $this->assertAuthenticatedAs($customer);
    }

    public function test_admin_precreated_customer_logs_in_with_same_email(): void
    {
        $customer = User::factory()->customer()->create([
            'email' => 'adminmade@example.com',
            'phone' => '9988776655',
            'registered_via' => RegisteredVia::ADMIN_CREATED,
        ]);

        $code = $this->sendAndCaptureOtp($customer->email);

        $this->post(route('account.verify-otp.submit'), [
            'email' => $customer->email,
            'code' => $code,
        ])->assertRedirect(route('account.dashboard'));

        $this->assertAuthenticatedAs($customer);
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

        $this->post(route('account.verify-otp.submit'), [
            'email' => $email,
            'code' => $code,
        ]);

        $this->post(route('account.register.submit'), [
            'name' => 'Walk In',
            'phone' => '9111222333',
        ])->assertRedirect(route('account.dashboard'));

        $existing->refresh();
        $this->assertSame($email, $existing->email);
        $this->assertNotNull($existing->email_claimed_at);
        $this->assertAuthenticatedAs($existing);
    }

    public function test_staff_cannot_use_customer_otp_login(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $admin->assignRole('Admin');

        $this->from(route('account.login'))
            ->post(route('account.login.send-otp'), ['email' => $admin->email])
            ->assertRedirect(route('account.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_deleted_customer_can_reregister_as_new_user(): void
    {
        $customer = User::factory()->customer()->create([
            'email' => 'reuse@example.com',
            'phone' => '9000000001',
        ]);
        $customer->delete();
        $customer->refresh();
        $this->assertStringContainsString('-deleted-', $customer->email);

        $code = $this->sendAndCaptureOtp('reuse@example.com');

        $this->post(route('account.verify-otp.submit'), [
            'email' => 'reuse@example.com',
            'code' => $code,
        ])->assertRedirect(route('account.register'));

        $this->post(route('account.register.submit'), [
            'name' => 'Fresh User',
            'phone' => '9000000001',
        ])->assertRedirect(route('account.dashboard'));

        $newUser = User::customers()->where('email', 'reuse@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertNotSame($customer->id, $newUser->id);
    }

    public function test_send_otp_is_rate_limited_per_email_after_repeated_requests(): void
    {
        Mail::fake();

        $email = 'ratelimit@example.com';

        for ($i = 0; $i < CustomerAuthService::SEND_OTP_EMAIL_MAX; $i++) {
            $this->post(route('account.login.send-otp'), ['email' => $email])
                ->assertRedirect(route('account.verify-otp', ['email' => $email]));
        }

        $this->post(route('account.login.send-otp'), ['email' => $email])
            ->assertRedirect(route('account.login'))
            ->assertSessionHasErrors('email');
    }

    public function test_invalid_email_does_not_count_toward_send_otp_rate_limit(): void
    {
        Mail::fake();

        for ($i = 0; $i < 10; $i++) {
            $this->post(route('account.login.send-otp'), ['email' => 'not-an-email'])
                ->assertRedirect(route('account.login'))
                ->assertSessionHasErrors('email');
        }

        $this->post(route('account.login.send-otp'), ['email' => 'valid@example.com'])
            ->assertRedirect(route('account.verify-otp', ['email' => 'valid@example.com']));
    }
}
