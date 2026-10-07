<?php

declare(strict_types=1);

namespace App\Actions\Route;

use App\Caching\RouteCache;
use App\Data\Entity\Route\Route;
use App\DTOs\RouteDTO;
use App\Tasks\Route\GetAllRouteTask;

final readonly class GetAllRouteAction
{
    public function __construct(
        private GetAllRouteTask $getAllRouteTask,
        private RouteCache $cache,
    ) {}

    /**
     * @return list<RouteDTO>
     */
    public function run(): array
    {
        return $this->cache->get(
            key: 'routes',
            callback: function (): array {
                $routes = $this->getAllRouteTask->run();

                return array_map(
                    fn(Route $route) => new RouteDTO(
                        id: $route->id,
                        shortName: $route->shortName,
                        longName: $route->longName,
                    ),
                    $routes,
                );
            },
        );

    }
}
