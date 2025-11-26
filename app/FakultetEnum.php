<?php

namespace App;

enum FakultetEnum: string
{
    case TIBBIYOT = 'Tibbiyot fakulteti';
    case PEDAGOGIKA = 'Pedagogika va ijtimoiy-gumanitar fanlar fakulteti';
    case IQTISODIYOT = 'Iqtisodiyot va axborot texnologiyalari fakulteti';

    public static function options(): array
    {
        return [
            self::TIBBIYOT->value => self::TIBBIYOT->value,
            self::PEDAGOGIKA->value => self::PEDAGOGIKA->value,
            self::IQTISODIYOT->value => self::IQTISODIYOT->value,
        ];
    }
}