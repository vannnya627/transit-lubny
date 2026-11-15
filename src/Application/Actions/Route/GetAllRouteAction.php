<?php

declare(strict_types=1);

namespace App\Application\Actions\Route;

use App\Application\Caching\RouteCacheInterface;
use App\Application\DTOs\RouteDTO;
use App\Application\Tasks\Route\GetAllRouteTask;

final readonly class GetAllRouteAction
{
    public function __construct(
        private GetAllRouteTask $getAllRouteTask,
        private RouteCacheInterface $cache,
    ) {}

    /**
     * @return list<RouteDTO>
     */
    public function run(): array
    {
        return $this->cache->getAllRoutes(
            callback: function (): array {
                $routes = $this->getAllRouteTask->run();

                return array_map(RouteDTO::create(...), $routes);
            },
        );

    }
}
