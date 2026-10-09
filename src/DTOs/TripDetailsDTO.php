<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Data\Entity\Route\Pattern;
use App\Data\Entity\Trip\Trip;

final readonly class TripDetailsDTO
{
    /**
     * @param list<StopForTripDTO> $stops
     */
    private function __construct(
        public int $tripId,
        public RouteDTO $route,
        public string $headsign,
        public array $stops,
    ) {}

    /**
     * @param list<StopForTripDTO> $stops
     */
    public static function create(Trip $trip, Pattern $pattern, array $stops): TripDetailsDTO
    {
        return new self(
            tripId: $trip->id,
            route: RouteDTO::create($pattern->route),
            headsign: $pattern->name,
            stops: $stops,
        );
    }
}
