<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\PusherSettingsResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PusherSettingsResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_apply_broadcasting_config_syncs_host_with_db_cluster(): void
    {
        Setting::set('notifications_enabled', true);
        Setting::setEncrypted('pusher_app_id', 'test-app-id');
        Setting::setEncrypted('pusher_app_key', 'test-app-key');
        Setting::setEncrypted('pusher_app_secret', 'test-app-secret');
        Setting::setEncrypted('pusher_app_cluster', 'ap2');

        config([
            'broadcasting.connections.pusher.options.cluster' => 'mt1',
            'broadcasting.connections.pusher.options.host' => 'api-mt1.pusher.com',
        ]);

        $this->app->make(PusherSettingsResolver::class)->applyBroadcastingConfig();

        $this->assertSame('ap2', config('broadcasting.connections.pusher.options.cluster'));
        $this->assertSame('api-ap2.pusher.com', config('broadcasting.connections.pusher.options.host'));
    }

    public function test_pusher_options_preserves_custom_non_pusher_cloud_host(): void
    {
        config(['broadcasting.connections.pusher.options.host' => '127.0.0.1']);

        Setting::setEncrypted('pusher_app_id', 'test-app-id');
        Setting::setEncrypted('pusher_app_key', 'test-app-key');
        Setting::setEncrypted('pusher_app_secret', 'test-app-secret');
        Setting::setEncrypted('pusher_app_cluster', 'ap2');

        $this->app->make(PusherSettingsResolver::class)->applyBroadcastingConfig();

        $this->assertSame('127.0.0.1', config('broadcasting.connections.pusher.options.host'));
        $this->assertSame('ap2', config('broadcasting.connections.pusher.options.cluster'));
    }
}
