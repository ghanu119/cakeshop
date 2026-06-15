<?php

namespace Tests\Feature;

use App\Mail\CustomerLoginOtp;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendOtpRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_send_otp_redirects_to_verify_page_with_email(): void
    {
        Mail::fake();

        $email = 'shopper@example.com';

        $response = $this->post(route('account.login.send-otp'), [
            'email' => $email,
        ]);

        $response->assertRedirect(route('account.verify-otp', ['email' => $email]));
        Mail::assertSent(CustomerLoginOtp::class);
    }

    public function test_account_form_errors_redirect_back_to_login_not_home(): void
    {
        $response = $this
            ->from(route('home'))
            ->post(route('account.login.send-otp'), [
                'email' => 'not-an-email',
            ]);

        $response->assertRedirect(route('account.login'));
        $response->assertSessionHasErrors('email');
    }
}
