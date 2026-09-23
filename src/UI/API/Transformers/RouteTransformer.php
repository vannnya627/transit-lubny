<?php

declare(strict_types=1);

namespace App\UI\API\Transformers;

use App\DTOs\RouteDTO;
use App\UI\API\Responses\RouteResponse;

final readonly class RouteTransformer
{
    public function transform(RouteDTO $routeDTO): RouteResponse
    {
        return RouteResponse::create($routeDTO);
    }
}
