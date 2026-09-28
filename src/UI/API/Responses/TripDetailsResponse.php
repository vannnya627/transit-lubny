<?php

declare(strict_types=1);

namespace App\UI\API\Responses;

final readonly class TripDetailsResponse
{
    /**
     * @param list<StopForTripResponse> $stops
     */
    public function __construct(
        public int $tripId,
        public RouteResponse $route,
        public string $headsign,
        public array $stops,
    ) {}
}
