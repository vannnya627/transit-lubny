<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enum\Direction;

final readonly class PatternDTO
{
    public function __construct(
        public int $id,
        public Direction $direction,
        public string $name,
    ) {}
}
