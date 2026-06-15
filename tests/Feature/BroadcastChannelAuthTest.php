<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\PusherSettingsResolver;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadcastChannelAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        Setting::set('notifications_enabled', true);
        Setting::setEncrypted('pusher_app_id', 'test-app-id');
        Setting::setEncrypted('pusher_app_key', 'test-app-key');
        Setting::setEncrypted('pusher_app_secret', 'test-app-secret');
        Setting::setEncrypted('pusher_app_cluster', 'ap2');

        $this->app->make(PusherSettingsResolver::class)->applyBroadcastingConfig();
    }

    public function test_verified_admin_can_authorize_private_user_channel(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');
        $admin->refresh();

        $this->assertTrue($admin->hasAnyRole(['Admin', 'Kitchen']));

        $response = $this->actingAs($admin)->post('/broadcasting/auth', [
            'channel_name' => 'private-App.Models.User.'.$admin->id,
            'socket_id' => '1234.5678',
        ]);

        $response->assertOk();
    }

    public function test_staff_without_role_cannot_authorize_private_user_channel(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson('/broadcasting/auth', [
            'channel_name' => 'private-App.Models.User.'.$user->id,
            'socket_id' => '1234.5678',
        ]);

        $response->assertForbidden();
    }

    public function test_guest_cannot_authorize_private_user_channel(): void
    {
        $response = $this->postJson('/broadcasting/auth', [
            'channel_name' => 'private-App.Models.User.1',
            'socket_id' => '1234.5678',
        ]);

        $response->assertUnauthorized();
    }
}
