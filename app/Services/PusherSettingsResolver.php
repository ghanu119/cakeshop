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

        $options = $this->pusherOptions($config);

        config([
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher.key' => $config['key'],
            'broadcasting.connections.pusher.secret' => $config['secret'],
            'broadcasting.connections.pusher.app_id' => $config['app_id'],
            'broadcasting.connections.pusher.options.cluster' => $options['cluster'],
            'broadcasting.connections.pusher.options.host' => $options['host'],
            'broadcasting.connections.pusher.options.useTLS' => $options['useTLS'],
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
     * @param  array{key: string, secret: string, app_id: string, cluster: string}  $config
     * @return array{cluster: string, host: string, useTLS: true}
     */
    private function pusherOptions(array $config): array
    {
        $configuredHost = (string) config('broadcasting.connections.pusher.options.host', '');

        $host = filled($configuredHost) && ! preg_match('/^api-[a-z0-9]+\.pusher\.com$/', $configuredHost)
            ? $configuredHost
            : 'api-'.$config['cluster'].'.pusher.com';

        return [
            'cluster' => $config['cluster'],
            'host' => $host,
            'useTLS' => true,
        ];
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
            'options' => $this->pusherOptions($config),
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
                $this->pusherOptions($config)
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
