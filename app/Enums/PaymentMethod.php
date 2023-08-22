<?php

namespace App\Enums;

enum PaymentMethod: int
{
    case cash = 0;
    case card = 1;

    public static function getSelect(): array
    {
        $array = [];

        foreach (self::cases() as $case) {
            $array[$case->value] = __('common.payment_methods.' . $case->name);
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
