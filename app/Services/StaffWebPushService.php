<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Notifications\WebPushStaffAlertNotification;
use App\Support\StaffNotificationUrl;
use Throwable;

class StaffWebPushService
{
    /**
     * @param  array{title?: string, body?: string, url?: string, id?: string, type?: string|null}  $payload
     * @return array{sent: bool, error: string|null}
     */
    public function sendNow(User $user, array $payload): array
    {
        if (! Setting::isWebPushEnabled()) {
            return [
                'sent' => false,
                'error' => __('Browser push alerts are disabled in Settings.'),
            ];
        }

        if (! $user->pushSubscriptions()->exists()) {
            return [
                'sent' => false,
                'error' => __('No browser subscription on this device.'),
            ];
        }

        $message = [
            'title' => $payload['title'] ?? 'Cake Shop',
            'body' => $payload['body'] ?? '',
            'url' => StaffNotificationUrl::sanitize($payload['url'] ?? null),
        ];

        if (! empty($payload['id'])) {
            $message['id'] = $payload['id'];
        }

        if (array_key_exists('type', $payload) && $payload['type'] !== null) {
            $message['type'] = $payload['type'];
        }

        $subject = Setting::get('webpush_subject') ?: config('app.url');
        $subject = preg_replace('#^http:#i', 'https:', (string) $subject);

        config([
            'webpush.vapid.subject' => $subject,
            'webpush.vapid.public_key' => Setting::getWebPushPublicKey(),
            'webpush.vapid.private_key' => Setting::getWebPushPrivateKey(),
        ]);

        try {
            $user->notify(new WebPushStaffAlertNotification($message));

            return ['sent' => true, 'error' => null];
        } catch (Throwable $e) {
            report($e);

            return [
                'sent' => false,
                'error' => __('Push delivery failed: :message', ['message' => $e->getMessage()]),
            ];
        }
    }
}
