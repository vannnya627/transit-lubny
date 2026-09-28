<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class TripDetailsDTO
{
    /**
     * @param list<StopForTripDTO> $stops
     */
    public function __construct(
        public int $tripId,
        public RouteDTO $route,
        public string $headsign,
        public array $stops,
    ) {}
}
