<?php

declare(strict_types=1);

namespace App\UI\API\Responses;

final readonly class DepartureResponse
{
    public function __construct(
        public int $tripId,
        public RouteResponse $route,
        public string $headsign,
        public string $arrivalTime,
        public int $expectedInMinutes,
    ) {}
}
