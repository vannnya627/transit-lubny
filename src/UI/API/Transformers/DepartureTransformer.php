<?php

declare(strict_types=1);

namespace App\UI\API\Transformers;

use App\DTOs\DepartureDTO;
use App\UI\API\Responses\DepartureResponse;

final readonly class DepartureTransformer
{
    public function __construct(
        private RouteTransformer $routeTransformer,
    ) {}

    public function transform(DepartureDTO $dto): DepartureResponse
    {
        return new DepartureResponse(
            tripId: $dto->tripId,
            route: $this->routeTransformer->transform($dto->route),
            headsign: $dto->headsign,
            arrivalTime: $dto->arrivalTime,
            expectedInMinutes: $dto->expectedInMinutes,
        );
    }
}
