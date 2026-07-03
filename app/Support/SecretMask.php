<?php

namespace App\Support;

class SecretMask
{
    public static function mask(?string $value, int $visibleSuffix = 4): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $length = strlen($value);

        if ($length <= $visibleSuffix) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - $visibleSuffix).substr($value, -$visibleSuffix);
    }
}
