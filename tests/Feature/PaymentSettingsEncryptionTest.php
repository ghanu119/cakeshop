<?php

namespace Tests\Feature;

use App\Models\Setting;
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
}
