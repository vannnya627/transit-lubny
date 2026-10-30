<?php

declare(strict_types=1);

namespace App\Application\Actions\Route;

use App\Application\Caching\RouteCache;
use App\Application\DTOs\PatternDTO;
use App\Application\DTOs\RouteWithPatternsDTO;
use App\Application\Tasks\Route\GetRouteTask;
use App\Domain\Entity\Route\Pattern;

final readonly class GetRouteAction
{
    public function __construct(
        private GetRouteTask $getRouteTask,
        private RouteCache $cache,
    ) {}

    public function run(int $routeId): RouteWithPatternsDTO
    {
        return $this->cache->get(
            key: "routes.$routeId",
            callback: function () use ($routeId): RouteWithPatternsDTO {
                $route = $this->getRouteTask->run($routeId);

                /** @var list<Pattern> $patterns */
                $patterns = array_values($route->patterns->toArray());

                $patternDTOs = array_map(PatternDTO::create(...), $patterns);

                return RouteWithPatternsDTO::create($route, $patternDTOs);
            },
        );
    }
}
