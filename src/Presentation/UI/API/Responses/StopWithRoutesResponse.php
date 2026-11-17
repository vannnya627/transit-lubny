<?php

declare(strict_types=1);

namespace App\Presentation\UI\API\Responses;

final readonly class StopWithRoutesResponse
{
    /**
     * @param list<RouteResponse> $routes
     */
    public function __construct(
        public StopResponse $stop,
        public array $routes,
    ) {}
}
