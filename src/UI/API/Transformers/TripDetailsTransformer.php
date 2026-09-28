<?php

declare(strict_types=1);

namespace App\UI\API\Transformers;

use App\DTOs\TripDetailsDTO;
use App\UI\API\Responses\TripDetailsResponse;

final readonly class TripDetailsTransformer
{
    public function __construct(
        private RouteTransformer $routeTransformer,
        private StopForTripTransformer $stopTransformer,
    ) {}

    public function transform(TripDetailsDTO $tripDetailsDTO): TripDetailsResponse
    {
        return new TripDetailsResponse(
            tripId: $tripDetailsDTO->tripId,
            route: $this->routeTransformer->transform($tripDetailsDTO->route),
            headsign: $tripDetailsDTO->headsign,
            stops: array_map($this->stopTransformer->transform(...), $tripDetailsDTO->stops),
        );
    }
}
