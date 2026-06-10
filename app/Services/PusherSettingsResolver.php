<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Broadcasting\BroadcastManager;
use Pusher\Pusher;
use Throwable;

class PusherSettingsResolver
{
    public function isEnabled(): bool
    {
        return Setting::isNotificationsEnabled() && Setting::isPusherConfigured();
    }

    public function connectionName(): string
    {
        return $this->isEnabled() ? 'pusher' : 'null';
    }

    public function applyBroadcastingConfig(): void
    {
        $config = Setting::pusherConfig();

        if ($config === null) {
            return;
        }

        config([
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher.key' => $config['key'],
            'broadcasting.connections.pusher.secret' => $config['secret'],
            'broadcasting.connections.pusher.app_id' => $config['app_id'],
            'broadcasting.connections.pusher.options.cluster' => $config['cluster'],
            'broadcasting.connections.pusher.options.useTLS' => true,
        ]);

        $this->registerBroadcastChannels();
    }

    private function registerBroadcastChannels(): void
    {
        app(BroadcastManager::class)->forgetDrivers();

        $channelsFile = base_path('routes/channels.php');

        if (is_file($channelsFile)) {
            require $channelsFile;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastingOptions(): array
    {
        $config = Setting::pusherConfig();

        if ($config === null) {
            return [];
        }

        return [
            'key' => $config['key'],
            'secret' => $config['secret'],
            'app_id' => $config['app_id'],
            'options' => [
                'cluster' => $config['cluster'],
                'useTLS' => true,
            ],
        ];
    }

    /**
     * @return array{enabled: bool, key: ?string, cluster: string}
     */
    public function frontendConfig(): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'key' => Setting::getPusherKey(),
            'cluster' => Setting::getPusherCluster(),
        ];
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function testConnection(): array
    {
        if (! Setting::isPusherConfigured()) {
            return [
                'success' => false,
                'message' => __('Pusher is not fully configured. Please enter App ID, Key, Secret, and Cluster.'),
            ];
        }

        try {
            $config = Setting::pusherConfig();
            $pusher = new Pusher(
                $config['key'],
                $config['secret'],
                $config['app_id'],
                ['cluster' => $config['cluster'], 'useTLS' => true]
            );

            $pusher->trigger('settings-test', 'ping', ['ok' => true]);

            return [
                'success' => true,
                'message' => __('Pusher connection successful.'),
            ];
        } catch (Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => __('Could not connect to Pusher. Please check your credentials and try again.'),
            ];
        }
    }
}
