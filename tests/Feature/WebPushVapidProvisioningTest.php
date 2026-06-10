<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\WebPushVapidService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebPushVapidProvisioningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_enabling_web_push_auto_provisions_encrypted_vapid_keys(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');
        $admin->givePermissionTo('settings.manage');

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'notifications_web_push_enabled' => '1',
        ]);

        $response->assertRedirect();

        $this->assertTrue(Setting::isWebPushEnabled());
        $this->assertNotNull(Setting::getEncrypted('webpush_public_key'));
        $this->assertNotNull(Setting::getEncrypted('webpush_private_key'));
        $this->assertNotEmpty(Setting::get('webpush_subject'));
    }

    public function test_vapid_service_stores_keys_in_database(): void
    {
        app(WebPushVapidService::class)->ensureKeysProvisioned();

        $this->assertNotNull(Setting::getEncrypted('webpush_public_key'));
        $this->assertNotNull(Setting::getEncrypted('webpush_private_key'));
        $this->assertTrue(Setting::isWebPushEnabled() || filled(Setting::getWebPushPublicKey()));
    }
}
