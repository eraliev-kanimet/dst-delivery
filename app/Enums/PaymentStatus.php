<?php

namespace App\Enums;

enum PaymentStatus: int
{
    case success = 0;
    case confirmation_pending = 1;
    case failed = 2;

    public static function getSelect(): array
    {
        $array = [];

        foreach (self::cases() as $case) {
            $array[$case->value] = __('common.payment_status.' . $case->name);
        }

        return $array;
    }
}
