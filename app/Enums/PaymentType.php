<?php

namespace App\Enums;

enum PaymentType: int
{
    case card = 1;

    public static function getSelect(): array
    {
        $array = [];

        foreach (self::cases() as $case) {
            $array[$case->value] = __('common.payment_types.' . $case->name);
        }

        return $array;
    }

    public static function values(): array
    {
        $array = [];

        foreach (self::cases() as $case) {
            $array[] = $case->value;
        }

        return $array;
    }
}
