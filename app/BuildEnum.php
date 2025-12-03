<?php

namespace App;

enum BuildEnum: string
{
    case ASOSIY = 'Asosiy';
    case TIBBIYOT = 'Tibbiyot';
    case TISU_HUB = 'TISU HUB';
    case OBLASTNOY = 'Oblastnoy';
    case SELXOZ = 'Selxoz';

    public static function options(): array
    {
        return [
            self::ASOSIY->value => self::ASOSIY->value,
            self::TIBBIYOT->value => self::TIBBIYOT->value,
            self::TISU_HUB->value => self::TISU_HUB->value,
            self::OBLASTNOY->value => self::OBLASTNOY->value,
            self::SELXOZ->value => self::SELXOZ->value,
        ];
    }
}