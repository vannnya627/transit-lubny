<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Data\Entity\Route\Pattern;
use App\Enum\Direction;

final readonly class PatternDTO
{
    private function __construct(
        public int $id,
        public Direction $direction,
        public string $name,
    ) {}

    public static function create(Pattern $pattern): self
    {
        return new self(
            id: $pattern->id,
            direction: $pattern->direction,
            name: $pattern->name,
        );
    }
}
