<?php

namespace App\Enums;

enum DeliveryType: int
{
    case courier = 1;
    case self_delivery = 2;

    public static function getSelect(): array
    {
        $array = [];

        foreach (self::cases() as $case) {
            $array[$case->value] = __('common.delivery_types.' . $case->name);
        }

        return $array;
    }
}
