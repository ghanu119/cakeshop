<?php

namespace App\Models\User;

class UserGender
{
    public const MALE = 'male';

    public const FEMALE = 'female';

    public const OTHER = 'other';

    public const PREFER_NOT_TO_SAY = 'prefer_not_to_say';

    public static function options(): array
    {
        return [
            self::MALE => __('Male'),
            self::FEMALE => __('Female'),
            self::OTHER => __('Other'),
            self::PREFER_NOT_TO_SAY => __('Prefer not to say'),
        ];
    }
}
