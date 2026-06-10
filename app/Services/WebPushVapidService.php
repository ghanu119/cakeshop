<?php

namespace App\Services;

use App\Models\Setting;
use Minishlink\WebPush\VAPID;

class WebPushVapidService
{
    public function ensureKeysProvisioned(): void
    {
        if (Setting::getEncrypted('webpush_public_key') && Setting::getEncrypted('webpush_private_key')) {
            $this->ensureSubject();

            return;
        }

        $publicKey = env('VAPID_PUBLIC_KEY');
        $privateKey = env('VAPID_PRIVATE_KEY');

        if (filled($publicKey) && filled($privateKey)) {
            Setting::setEncrypted('webpush_public_key', $publicKey);
            Setting::setEncrypted('webpush_private_key', $privateKey);
        } else {
            $keys = VAPID::createVapidKeys();
            Setting::setEncrypted('webpush_public_key', $keys['publicKey']);
            Setting::setEncrypted('webpush_private_key', $keys['privateKey']);
        }

        $this->ensureSubject();
        Setting::flushCache();
    }

    private function ensureSubject(): void
    {
        if (filled(Setting::get('webpush_subject'))) {
            return;
        }

        $adminEmail = Setting::get('admin_email');
        $appUrl = preg_replace('#^http:#i', 'https:', (string) config('app.url'));

        $subject = filled($adminEmail)
            ? 'mailto:'.$adminEmail
            : (env('VAPID_SUBJECT') ?: $appUrl);

        Setting::set('webpush_subject', $subject);
    }
}
