<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class StopDTO
{
    public function __construct(
        public int $sequence,
        public int $stopId,
        public string $name,
    ) {}
}
