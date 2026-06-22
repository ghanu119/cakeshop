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

    /**
     * Normalize a staff notification target to a same-origin relative path.
     */
    public static function toAppPath(?string $url): string
    {
        $sanitized = self::sanitize($url);
        $path = parse_url($sanitized, PHP_URL_PATH) ?? '/admin/dashboard';
        $query = parse_url($sanitized, PHP_URL_QUERY);

        if ($query !== null && $query !== '') {
            return $path.'?'.$query;
        }

        return $path;
    }
}
