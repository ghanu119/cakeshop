<?php

namespace App\Support;

class PhoneNormalizer
{
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '91') && strlen($digits) > 10) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0') && strlen($digits) > 10) {
            $digits = ltrim($digits, '0');
        }

        return $digits !== '' ? $digits : null;
    }

    public static function mask(?string $phone): string
    {
        $normalized = self::normalize($phone);

        if ($normalized === null || strlen($normalized) < 4) {
            return '••••';
        }

        return '••••'.substr($normalized, -4);
    }
}
