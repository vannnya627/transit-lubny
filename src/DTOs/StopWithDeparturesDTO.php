<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class StopWithDeparturesDTO
{
    /**
     * @param list<DepartureDTO> $departures
     */
    public function __construct(
        public int $stopId,
        public string $stopName,
        public int $cityCode,
        public array $departures,
    ) {}
}
