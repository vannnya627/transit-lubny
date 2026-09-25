<?php

declare(strict_types=1);

namespace App\DTOs;

use App\ValueObjects\Coordinates;

final readonly class StopDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public Coordinates $coordinates,
    ) {}
}
