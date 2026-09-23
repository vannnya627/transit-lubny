<?php

declare(strict_types=1);

namespace App\Actions\Route;

use App\DTOs\RouteDTO;
use App\Entity\Route\Route;
use App\Tasks\Route\GetAllRouteTask;

final readonly class GetAllRouteAction
{
    public function __construct(
        private GetAllRouteTask $getAllRouteTask,
    ) {}

    /**
     * @return list<RouteDTO>
     */
    public function run(): array
    {
        $routes = $this->getAllRouteTask->run();

        return array_map(
            fn(Route $route) => new RouteDTO(
                id: $route->id,
                shortName: $route->shortName,
                longName: $route->longName,
            ),
            $routes,
        );
    }
}
