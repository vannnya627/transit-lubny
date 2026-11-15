<?php

declare(strict_types=1);

namespace App\Application\Caching;

use App\Application\DTOs\PatternWithStopsAndTripsDTO;
use App\Application\DTOs\RouteDTO;
use App\Application\DTOs\RouteWithPatternsDTO;

interface RouteCacheInterface
{
    /**
     * @param callable(): list<RouteDTO> $callback
     *
     * @return list<RouteDTO>
     */
    public function getAllRoutes(callable $callback): array;

    /**
     * @param callable(): PatternWithStopsAndTripsDTO $callback
     */
    public function getPatternWithStopsAndTrips(int $patternId, callable $callback): PatternWithStopsAndTripsDTO;

    /**
     * @param callable(): RouteWithPatternsDTO $callback
     */
    public function getRoute(int $routeId, callable $callback): RouteWithPatternsDTO;

    public function invalidate(): void;
}
