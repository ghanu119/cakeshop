<?php

namespace App\Support;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class ValidationRules
{
    public static function uniqueAmongActive(string $table, string $column, mixed $ignoreId = null): Unique
    {
        $rule = Rule::unique($table, $column)->whereNull('deleted_at');

        if ($ignoreId !== null) {
            $rule->ignore($ignoreId);
        }

        return $rule;
    }
}
