<?php

namespace App\Enums;

enum PaymentProvider: string
{
    case sberbank = 'sberbank';

    public static function values(): array
    {
        $array = [];

        foreach (self::cases() as $case) {
            $array[] = $case->value;
        }

        return $array;
    }
}
