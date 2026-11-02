<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Entity\Pattern\Pattern;
use App\Domain\Entity\Trip\Trip;

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
    public static function create(Trip $trip, Pattern $pattern, RouteDTO $route, array $stops): TripDetailsDTO
    {
        return new self(
            tripId: $trip->id,
            route: $route,
            headsign: $pattern->name,
            stops: $stops,
        );
    }

    /**
     * @param list<StopForTripDTO> $stops
     */
    public function withStops(array $stops): self
    {
        return new self(
            tripId: $this->tripId,
            route: $this->route,
            headsign: $this->headsign,
            stops: $stops,
        );
    }
}
