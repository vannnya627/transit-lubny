<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enum\Direction;

final readonly class PatternWithStopsAndTripsDTO
{
    /**
     * @param list<StopDTO> $stops
     * @param list<TripDTO> $trips
     */
    public function __construct(
        public int $id,
        public Direction $direction,
        public string $name,
        public array $stops,
        public array $trips,
    ) {}
}
