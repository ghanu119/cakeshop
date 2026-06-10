<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Notifications\WebPushStaffAlertNotification;
use App\Support\StaffNotificationUrl;
use Throwable;

class StaffWebPushService
{
    public function sendNow(User $user, array $payload): void
    {
        if (! Setting::isWebPushEnabled()) {
            return;
        }

        if (! $user->pushSubscriptions()->exists()) {
            return;
        }

        $message = [
            'title' => $payload['title'] ?? 'Cake Shop',
            'body' => $payload['body'] ?? '',
            'url' => StaffNotificationUrl::sanitize($payload['url'] ?? null),
        ];

        $subject = Setting::get('webpush_subject') ?: config('app.url');
        $subject = preg_replace('#^http:#i', 'https:', (string) $subject);

        config([
            'webpush.vapid.subject' => $subject,
            'webpush.vapid.public_key' => Setting::getWebPushPublicKey(),
            'webpush.vapid.private_key' => Setting::getWebPushPrivateKey(),
        ]);

        try {
            $user->notify(new WebPushStaffAlertNotification($message));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
