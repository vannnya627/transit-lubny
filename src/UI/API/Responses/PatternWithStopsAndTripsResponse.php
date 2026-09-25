<?php

declare(strict_types=1);

namespace App\UI\API\Responses;

final readonly class PatternWithStopsAndTripsResponse
{
    /**
     * @param list<StopForScheduleResponse> $stops
     * @param list<TripResponse>            $trips
     */
    public function __construct(
        public int $id,
        public string $direction,
        public string $name,
        public array $stops,
        public array $trips,
    ) {}
}
