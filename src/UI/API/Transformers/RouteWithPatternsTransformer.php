<?php

declare(strict_types=1);

namespace App\UI\API\Transformers;

use App\DTOs\RouteWithPatternsDTO;
use App\UI\API\Responses\RouteWithPatternsResponse;

final readonly class RouteWithPatternsTransformer
{
    public function __construct(
        private PatternTransformer $transformer,
    ) {}

    public function transform(RouteWithPatternsDTO $routeDTO): RouteWithPatternsResponse
    {
        return new RouteWithPatternsResponse(
            id: $routeDTO->id,
            shortName: $routeDTO->shortName,
            longName: $routeDTO->longName,
            patterns: array_map($this->transformer->transformer(...), $routeDTO->patterns),
        );
    }
}
