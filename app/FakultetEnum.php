<?php

namespace App;

enum FakultetEnum: string
{
    case TIBBIYOT = 'TIBBIYOT';
    case PEDAGOGIKA = 'PEDAGOGIKA';
    case IQTISODIYOT = "Iqtisodiyot";

    public static function options(): array
    {
        return [
            self::TIBBIYOT->value => self::TIBBIYOT->value,
            self::PEDAGOGIKA->value => self::PEDAGOGIKA->value,
            self::IQTISODIYOT->value => self::IQTISODIYOT->value,
        ];
    }
}