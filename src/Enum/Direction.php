<?php

declare(strict_types=1);

namespace App\Enum;

enum Direction: int
{
    case FORWARD  = 1;
    case RETURN   = 0;

    public function getLabel(): string
    {
        return match ($this) {
            self::FORWARD => 'Прямо',
            self::RETURN  => 'Назад',
        };
    }
}
