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

    public static function isValidIndianMobile(?string $phone): bool
    {
        $normalized = self::normalize($phone);

        return $normalized !== null
            && strlen($normalized) === 10
            && (bool) preg_match('/^[6-9]\d{9}$/', $normalized);
    }

    /**
     * Build a dial-ready international number (digits only, no plus) by prefixing
     * the given country code to the normalized local number. Used only for sending.
     */
    public static function toE164(?string $phone, string $countryCode = '91'): ?string
    {
        $normalized = self::normalize($phone);

        if ($normalized === null) {
            return null;
        }

        $code = preg_replace('/\D+/', '', $countryCode) ?? '';

        return $code.$normalized;
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
