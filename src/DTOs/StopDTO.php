<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Data\Entity\Stop;
use App\Data\ValueObjects\Coordinates;

final readonly class StopDTO
{
    private function __construct(
        public int $id,
        public string $name,
        public Coordinates $coordinates,
        public int $cityCode,
    ) {}

    public static function create(Stop $stop): self
    {
        return new self(
            id: $stop->id,
            name: $stop->name,
            coordinates: $stop->coordinates,
            cityCode: $stop->cityCode,
        );
    }
}
