<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Entity\Route\Pattern\Pattern;
use App\Domain\Entity\Trip\StopTime;
use App\Domain\ValueObjects\TimeOfDay;

final readonly class DepartureDTO
{
    private function __construct(
        public int $tripId,
        public RouteDTO $route,
        public string $headsign,
        public string $arrivalTime,
        public int $expectedInMinutes,
    ) {}

    public static function create(Pattern $pattern, RouteDTO $route, TimeOfDay $currentTime, StopTime $stopTime): self
    {
        return new self(
            tripId: $stopTime->trip->id,
            route: $route,
            headsign: $pattern->name,
            arrivalTime: $stopTime->getFormattedArrivalTime(),
            expectedInMinutes: $stopTime->getMinutesUntil($currentTime),
        );
    }
}
