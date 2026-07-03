<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\Payments\PaymentSettingsResolver;
use App\Support\SecretMask;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class PaymentSettingsEncryptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_encrypted_round_trip_for_razorpay_key_id(): void
    {
        Setting::setEncrypted('razorpay_key_id', 'rzp_test_key');

        $this->assertSame('rzp_test_key', Setting::getRazorpayKeyId());
    }

    public function test_encrypted_round_trip_for_razorpay_key_secret(): void
    {
        Setting::setEncrypted('razorpay_key_secret', 'rzp_test_secret');

        $this->assertSame('rzp_test_secret', Setting::getRazorpayKeySecret());
    }

    public function test_blank_form_submission_preserves_existing_encrypted_value(): void
    {
        Setting::setEncrypted('razorpay_key_secret', 'keep-me');

        Setting::setEncrypted('razorpay_key_secret', '');

        $this->assertSame('keep-me', Setting::getRazorpayKeySecret());
    }

    public function test_all_cached_returns_ciphertext_not_plaintext(): void
    {
        Setting::setEncrypted('razorpay_key_id', 'secret-key-value');

        $cached = Setting::allCached();

        $this->assertNotSame('secret-key-value', $cached['razorpay_key_id']);
        $this->assertSame('secret-key-value', Crypt::decryptString($cached['razorpay_key_id']));
    }

    public function test_is_razorpay_configured_false_when_keys_missing(): void
    {
        $this->assertFalse(Setting::isRazorpayConfigured());
    }

    public function test_is_razorpay_configured_true_when_both_keys_set(): void
    {
        Setting::setEncrypted('razorpay_key_id', 'rzp_test');
        Setting::setEncrypted('razorpay_key_secret', 'secret_test');

        $this->assertTrue(Setting::isRazorpayConfigured());
    }

    public function test_secret_mask_shows_visible_suffix(): void
    {
        $this->assertNull(SecretMask::mask(null));
        $this->assertSame('****', SecretMask::mask('abcd', 4));
        $this->assertSame('*********abc12', SecretMask::mask('rzp_test_abc12', 5));
    }

    public function test_masked_encrypted_value_returns_masked_hint(): void
    {
        Setting::setEncrypted('razorpay_key_id', 'rzp_test_abc12');
        Setting::setEncrypted('razorpay_key_secret', 'secret6789');

        $this->assertSame('*********abc12', Setting::maskedEncryptedValue('razorpay_key_id', 5));
        $this->assertSame('******6789', Setting::maskedEncryptedValue('razorpay_key_secret', 4));
        $this->assertNull(Setting::maskedEncryptedValue('pusher_app_key', 4));
    }

    public function test_set_encrypted_with_null_clears_stored_value(): void
    {
        Setting::setEncrypted('razorpay_key_id', 'rzp_test');
        Setting::setEncrypted('razorpay_key_secret', 'secret_test');

        Setting::setEncrypted('razorpay_key_id', null);
        Setting::setEncrypted('razorpay_key_secret', null);

        $this->assertFalse(Setting::hasEncryptedValue('razorpay_key_id'));
        $this->assertFalse(Setting::hasEncryptedValue('razorpay_key_secret'));
        $this->assertFalse(Setting::isRazorpayConfigured());
    }

    public function test_get_payment_gateway_defaults_to_razorpay_when_unset(): void
    {
        $this->assertSame('razorpay', Setting::getPaymentGateway());
    }

    public function test_get_payment_gateway_returns_null_when_explicitly_disabled(): void
    {
        Setting::set('payment_gateway', null);

        $this->assertNull(Setting::getPaymentGateway());
    }

    public function test_admin_can_disable_payment_gateway_via_settings_form(): void
    {
        $admin = $this->adminWithSettingsPermission();

        Setting::set('payment_gateway', 'razorpay');
        Setting::setEncrypted('razorpay_key_id', 'rzp_test');
        Setting::setEncrypted('razorpay_key_secret', 'secret_test');

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'payment_gateway' => '',
        ]);

        $response->assertRedirect(route('admin.settings.index'));

        $this->assertNull(Setting::getPaymentGateway());
        $this->assertFalse(app(PaymentSettingsResolver::class)->isOnlineCheckoutEnabled());
    }

    public function test_admin_can_clear_all_razorpay_credentials_via_settings_form(): void
    {
        $admin = $this->adminWithSettingsPermission();

        Setting::setEncrypted('razorpay_key_id', 'rzp_test');
        Setting::setEncrypted('razorpay_key_secret', 'secret_test');

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'clear_razorpay_credentials' => '1',
        ]);

        $response->assertRedirect(route('admin.settings.index'));

        $this->assertFalse(Setting::hasEncryptedValue('razorpay_key_id'));
        $this->assertFalse(Setting::hasEncryptedValue('razorpay_key_secret'));
        $this->assertFalse(Setting::isRazorpayConfigured());
    }

    public function test_new_credentials_win_when_clear_and_replace_submitted_together(): void
    {
        $admin = $this->adminWithSettingsPermission();

        Setting::setEncrypted('razorpay_key_id', 'old_key');
        Setting::setEncrypted('razorpay_key_secret', 'old_secret');

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'clear_razorpay_credentials' => '1',
            'razorpay_key_id' => 'new_key_id',
            'razorpay_key_secret' => 'new_secret',
        ]);

        $response->assertRedirect(route('admin.settings.index'));

        $this->assertSame('new_key_id', Setting::getRazorpayKeyId());
        $this->assertSame('new_secret', Setting::getRazorpayKeySecret());
        $this->assertTrue(Setting::isRazorpayConfigured());
    }

    private function adminWithSettingsPermission(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');
        $admin->givePermissionTo('settings.manage');

        return $admin;
    }
}
