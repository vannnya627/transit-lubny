<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Data\Entity\Route\Pattern;
use App\Enum\Direction;

final readonly class PatternWithStopsAndTripsDTO
{
    /**
     * @param list<StopForScheduleDTO> $stops
     * @param list<TripDTO>            $trips
     */
    private function __construct(
        public int $id,
        public Direction $direction,
        public string $name,
        public array $stops,
        public array $trips,
    ) {}

    /**
     * @param list<StopForScheduleDTO> $stops
     * @param list<TripDTO>            $trips
     */
    public static function create(Pattern $pattern, array $stops, array $trips): self
    {
        return new self(
            id: $pattern->id,
            direction: $pattern->direction,
            name: $pattern->name,
            stops: $stops,
            trips: $trips,
        );
    }
}
