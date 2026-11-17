<?php

declare(strict_types=1);

namespace App\Presentation\UI\API\Transformers;

use App\Application\DTOs\StopWithRoutesDTO;
use App\Presentation\UI\API\Responses\StopWithRoutesResponse;

final readonly class StopWithRoutesTransformer
{
    public function __construct(
        private StopTransformer $stopTransformer,
        private RouteTransformer $routeTransformer,
    ) {}

    public function transform(StopWithRoutesDTO $stopWithRoutesDTO): StopWithRoutesResponse
    {
        return new StopWithRoutesResponse(
            stop: $this->stopTransformer->transform($stopWithRoutesDTO->stopDTO),
            routes: array_map($this->routeTransformer->transform(...), $stopWithRoutesDTO->routesDTO),
        );
    }
}
