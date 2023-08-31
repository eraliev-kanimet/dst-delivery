<?php

namespace App\Enums;

enum OrderStatus: int
{
    case inactive = 0;
    case pending_payment = 1;
    case processing = 2;
    case confirmed = 3;
    case shipped = 4;
    case delivered = 5;
    case received = 6;
    case canceled = 7;

    public static function getSelect(): array
    {
        $array = [];

        foreach (self::cases() as $case) {
            $array[$case->value] = __('common.order_status.' . $case->name);
        }

        return $array;
    }

    public static function values(): array
    {
        $array = [];

        foreach (array_slice(self::cases(), 1) as $case) {
            $array[] = $case->value;
        }

        return $array;
    }
}
