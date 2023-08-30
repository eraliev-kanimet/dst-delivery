<?php

namespace App\Enums;

enum Role: int
{
    case admin = 1;
    case store_owner = 2;
    case store_manager = 3;

    public static function getSelect(): array
    {
        $array = [];

        foreach (self::cases() as $case) {
            $array[$case->value] = __('common.roles.' . $case->name);
        }

        return $array;
    }
}
