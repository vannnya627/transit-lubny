<?php

declare(strict_types=1);

namespace App\UI\API\Transformers;

use App\DTOs\StopWithDeparturesDTO;
use App\UI\API\Responses\StopWithDeparturesResponse;

final readonly class StopWithDeparturesTransformer
{
    public function __construct(
        private DepartureTransformer $departureTransformer,
    ) {}

    public function transform(StopWithDeparturesDTO $dto): StopWithDeparturesResponse
    {
        return new StopWithDeparturesResponse(
            stopId: $dto->stopId,
            stopName: $dto->stopName,
            cityCode: $dto->cityCode,
            departures: array_map($this->departureTransformer->transform(...), $dto->departures),
        );
    }
}
