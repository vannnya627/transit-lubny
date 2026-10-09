<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Data\Entity\Stop;

final readonly class StopWithDeparturesDTO
{
    /**
     * @param list<DepartureDTO> $departures
     */
    private function __construct(
        public int $stopId,
        public string $stopName,
        public int $cityCode,
        public array $departures,
    ) {}

    /**
     * @param list<DepartureDTO> $departures
     */
    public static function create(Stop $stop, array $departures): self
    {
        return new self(
            stopId: $stop->id,
            stopName: $stop->name,
            cityCode: $stop->cityCode,
            departures: $departures,
        );
    }
}
