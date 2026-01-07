<?php

namespace App;

enum LessonType: int
{
    case MARUZA = 1;
    case AMALIY = 2;
    case LABARATORIYA = 3;
    case SEMINAR = 4;

    public function label(): string
    {
        return match ($this) {
            self::MARUZA => 'Ma`ruza',
            self::AMALIY => 'Amaliy',
            self::LABARATORIYA => 'Labaratoriya',
            self::SEMINAR => 'Seminar',
        };
    }

    public static function list(): array
    {
        $items = [];

        foreach (self::cases() as $case) {
            $items[$case->value] = $case->label();
        }

        return $items;
    }
}