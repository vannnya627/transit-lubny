<?php

declare(strict_types=1);

namespace App\Application\Actions\Stop;

use App\Application\Caching\StopCacheInterface;
use App\Application\DTOs\RouteDTO;
use App\Application\DTOs\StopDTO;
use App\Application\DTOs\StopWithRoutesDTO;
use App\Application\Tasks\Stop\FindRoutesByStopIdTask;
use App\Application\Tasks\Stop\GetStopByIdTask;

final readonly class GetStopWithRoutesAction
{
    public function __construct(
        private GetStopByIdTask $getStopByIdTask,
        private FindRoutesByStopIdTask $findRoutesByStopIdTask,
        private StopCacheInterface $cache,
    ) {}

    public function run(int $stopId): StopWithRoutesDTO
    {
        return $this->cache->getStopWithRoutes(
            stopId: $stopId,
            callback: function () use ($stopId) {
                $stop = $this->getStopByIdTask->run($stopId);
                $routes = $this->findRoutesByStopIdTask->run($stopId);

                $routesDTO = array_map(RouteDTO::create(...), $routes);

                return StopWithRoutesDTO::create(StopDTO::create($stop), $routesDTO);
            },
        );
    }
}
