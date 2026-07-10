<?php

namespace Tests\Feature;

use App\Livewire\Account\ProfileEmailVerification;
use App\Mail\CustomerLoginOtp;
use App\Models\User;
use App\Models\User\UserGender;
use App\Support\AuthGuards;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_customer_can_update_profile_marketing_fields(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer, AuthGuards::CUSTOMER)
            ->put(route('account.profile.update'), [
                'name' => 'Updated Name',
                'birth_day' => 15,
                'birth_month' => 6,
                'anniversary_day' => 20,
                'anniversary_month' => 12,
                'gender' => UserGender::FEMALE,
            ])
            ->assertRedirect(route('account.profile.edit'));

        $customer->refresh();
        $this->assertSame('Updated Name', $customer->name);
        $this->assertSame(15, $customer->birth_day);
        $this->assertSame(UserGender::FEMALE, $customer->gender);
    }

    public function test_phone_only_customer_can_add_email_with_otp(): void
    {
        Mail::fake();

        $customer = User::factory()->customer()->whatsappVerified()->create([
            'name' => 'Phone Only',
        ]);

        $newEmail = 'newemail@example.com';

        Livewire::actingAs($customer, AuthGuards::CUSTOMER)
            ->test(ProfileEmailVerification::class)
            ->set('email', $newEmail)
            ->call('sendOtp')
            ->assertSet('step', 'otp');

        $code = null;
        Mail::assertSent(CustomerLoginOtp::class, function (CustomerLoginOtp $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        Livewire::actingAs($customer, AuthGuards::CUSTOMER)
            ->test(ProfileEmailVerification::class)
            ->set('email', $newEmail)
            ->set('step', 'otp')
            ->set('code', $code)
            ->call('verifyOtp')
            ->assertSet('currentEmail', $newEmail);

        $customer->refresh();
        $this->assertSame($newEmail, $customer->email);
        $this->assertNotNull($customer->email_verified_at);
        $this->assertNotNull($customer->email_claimed_at);
    }

    public function test_customer_can_change_email_with_otp(): void
    {
        Mail::fake();

        $customer = User::factory()->customer()->create([
            'email' => 'old@example.com',
        ]);

        $newEmail = 'updated@example.com';

        Livewire::actingAs($customer, AuthGuards::CUSTOMER)
            ->test(ProfileEmailVerification::class)
            ->set('email', $newEmail)
            ->call('sendOtp')
            ->assertSet('step', 'otp');

        $code = null;
        Mail::assertSent(CustomerLoginOtp::class, function (CustomerLoginOtp $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        Livewire::actingAs($customer, AuthGuards::CUSTOMER)
            ->test(ProfileEmailVerification::class)
            ->set('email', $newEmail)
            ->set('step', 'otp')
            ->set('code', $code)
            ->call('verifyOtp')
            ->assertSet('currentEmail', $newEmail);

        $customer->refresh();
        $this->assertSame($newEmail, $customer->email);
        $this->assertNotNull($customer->email_verified_at);
    }

    public function test_customer_cannot_use_email_already_linked_to_another_account(): void
    {
        Mail::fake();

        $customer = User::factory()->customer()->whatsappVerified()->create();
        User::factory()->customer()->create(['email' => 'taken@example.com']);

        Livewire::actingAs($customer, AuthGuards::CUSTOMER)
            ->test(ProfileEmailVerification::class)
            ->set('email', 'taken@example.com')
            ->call('sendOtp')
            ->assertHasErrors(['email']);
    }
}
