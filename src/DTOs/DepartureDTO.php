<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Data\Entity\Route\Pattern;
use App\Data\Entity\Trip\Trip;

final readonly class DepartureDTO
{
    private function __construct(
        public int $tripId,
        public RouteDTO $route,
        public string $headsign,
        public string $arrivalTime,
        public int $expectedInMinutes,
    ) {}

    public static function create(Trip $trip, Pattern $pattern, RouteDTO $route, int $secondsFromMidnight): self
    {
        return new self(
            tripId: $trip->id,
            route: $route,
            headsign: $pattern->name,
            arrivalTime: $trip->getFirstStopTime()->getFormattedArrivalTime(),
            expectedInMinutes: $trip->getFirstStopTime()->getMinutesUntil($secondsFromMidnight),
        );
    }
}
