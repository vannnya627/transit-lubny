<?php

declare(strict_types=1);

namespace App\Presentation\UI\API\Transformers;

use App\Application\DTOs\RouteDTO;
use App\Presentation\UI\API\Responses\RouteResponse;

final readonly class RouteTransformer
{
    public function transform(RouteDTO $routeDTO): RouteResponse
    {
        return RouteResponse::create($routeDTO);
    }
}
