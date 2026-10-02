<?php

declare(strict_types=1);

namespace App\UI\API\Responses;

final readonly class StopWithDeparturesResponse
{
    /**
     * @param list<DepartureResponse> $departures
     */
    public function __construct(
        public int $stopId,
        public string $stopName,
        public ?int $cityCode,
        public array $departures,
    ) {}
}
