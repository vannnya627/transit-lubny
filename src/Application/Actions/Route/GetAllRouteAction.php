<?php

declare(strict_types=1);

namespace App\Application\Actions\Route;

use App\Application\Caching\RouteCache;
use App\Application\DTOs\RouteDTO;
use App\Application\Tasks\Route\GetAllRouteTask;

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

                return array_map(RouteDTO::create(...), $routes);
            },
        );

    }
}
