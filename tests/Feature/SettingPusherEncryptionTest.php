<?php

namespace Tests\Feature;

use App\Models\Setting;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class SettingPusherEncryptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_encrypted_round_trip_for_pusher_key(): void
    {
        Setting::setEncrypted('pusher_app_key', 'test-app-key');

        $this->assertSame('test-app-key', Setting::getPusherKey());
    }

    public function test_blank_form_submission_preserves_existing_encrypted_value(): void
    {
        Setting::setEncrypted('pusher_app_secret', 'keep-me');

        Setting::setEncrypted('pusher_app_secret', '');

        $this->assertSame('keep-me', Setting::getPusherSecret());
    }

    public function test_all_cached_returns_ciphertext_not_plaintext(): void
    {
        Setting::setEncrypted('pusher_app_key', 'secret-key-value');

        $cached = Setting::allCached();

        $this->assertNotSame('secret-key-value', $cached['pusher_app_key']);
        $this->assertSame('secret-key-value', Crypt::decryptString($cached['pusher_app_key']));
    }

    public function test_is_pusher_configured_false_when_keys_missing(): void
    {
        $this->assertFalse(Setting::isPusherConfigured());
    }

    public function test_seeder_enables_notifications_without_pusher_keys(): void
    {
        $this->seed(SettingSeeder::class);

        $this->assertSame('1', Setting::get('notifications_enabled'));
        $this->assertSame('0', Setting::get('notifications_web_push_enabled'));
        $this->assertFalse(Setting::isPusherConfigured());
        $this->assertNull(Setting::getEncrypted('pusher_app_key'));
    }
}
