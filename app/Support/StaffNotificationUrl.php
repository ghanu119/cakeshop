<?php

namespace App\Support;

class StaffNotificationUrl
{
    public static function sanitize(?string $url): string
    {
        if ($url === null || $url === '') {
            return route('admin.dashboard');
        }

        $parsed = parse_url($url);

        if ($parsed === false) {
            return route('admin.dashboard');
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $urlHost = $parsed['host'] ?? null;
        $path = $parsed['path'] ?? '/';

        if ($urlHost !== null && $appHost !== null && $urlHost !== $appHost) {
            return route('admin.dashboard');
        }

        if (! str_starts_with($path, '/admin')) {
            return route('admin.dashboard');
        }

        return $url;
    }
}
