<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class DepartureDTO
{
    public function __construct(
        public int $tripId,
        public RouteDTO $route,
        public string $headsign,
        public string $arrivalTime,
        public int $expectedInMinutes,
    ) {}
}
