<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
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

    public function test_masked_encrypted_value_returns_masked_hint_for_pusher_key(): void
    {
        Setting::setEncrypted('pusher_app_id', '1234567890');
        Setting::setEncrypted('pusher_app_key', 'abcdef1234');
        Setting::setEncrypted('pusher_app_secret', 'secret6789');

        $this->assertSame('******7890', Setting::maskedEncryptedValue('pusher_app_id', 4));
        $this->assertSame('******1234', Setting::maskedEncryptedValue('pusher_app_key', 4));
        $this->assertSame('******6789', Setting::maskedEncryptedValue('pusher_app_secret', 4));
    }

    public function test_admin_can_clear_all_pusher_credentials_via_settings_form(): void
    {
        $admin = $this->adminWithSettingsPermission();

        Setting::setEncrypted('pusher_app_id', '1234567');
        Setting::setEncrypted('pusher_app_key', 'test-key');
        Setting::setEncrypted('pusher_app_secret', 'test-secret');
        Setting::setEncrypted('pusher_app_cluster', 'ap2');

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'clear_pusher_credentials' => '1',
        ]);

        $response->assertRedirect(route('admin.settings.index'));

        $this->assertFalse(Setting::hasEncryptedValue('pusher_app_id'));
        $this->assertFalse(Setting::hasEncryptedValue('pusher_app_key'));
        $this->assertFalse(Setting::hasEncryptedValue('pusher_app_secret'));
        $this->assertFalse(Setting::hasEncryptedValue('pusher_app_cluster'));
        $this->assertFalse(Setting::isPusherConfigured());
    }

    public function test_new_credentials_win_when_clear_and_replace_submitted_together(): void
    {
        $admin = $this->adminWithSettingsPermission();

        Setting::setEncrypted('pusher_app_id', 'old-id');
        Setting::setEncrypted('pusher_app_key', 'old-key');
        Setting::setEncrypted('pusher_app_secret', 'old-secret');
        Setting::setEncrypted('pusher_app_cluster', 'mt1');

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'clear_pusher_credentials' => '1',
            'pusher_app_id' => 'new-id',
            'pusher_app_key' => 'new-key',
            'pusher_app_secret' => 'new-secret',
            'pusher_app_cluster' => 'ap2',
        ]);

        $response->assertRedirect(route('admin.settings.index'));

        $this->assertSame('new-id', Setting::getPusherAppId());
        $this->assertSame('new-key', Setting::getPusherKey());
        $this->assertSame('new-secret', Setting::getPusherSecret());
        $this->assertSame('ap2', Setting::getPusherCluster());
        $this->assertTrue(Setting::isPusherConfigured());
    }

    private function adminWithSettingsPermission(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');
        $admin->givePermissionTo('settings.manage');

        return $admin;
    }
}
