<?php

declare(strict_types=1);

namespace App\Infrastructure\Caches;

use App\Application\Caching\RouteCacheInterface;
use App\Application\DTOs\PatternWithStopsAndTripsDTO;
use App\Application\DTOs\RouteDTO;
use App\Application\DTOs\RouteWithPatternsDTO;
use App\Infrastructure\Caches\Abstracts\Cache;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\Cache\CacheInterface;

readonly class RouteCache extends Cache implements RouteCacheInterface
{
    public function __construct(
        #[Target(name: 'route.cache')]
        CacheInterface $cache,
        #[Target(name: 'route.cache')]
        CacheItemPoolInterface $cacheItemPool,
    ) {
        parent::__construct($cache, $cacheItemPool);
    }

    /**
     * @return list<RouteDTO>
     */
    public function getAllRoutes(callable $callback): array
    {
        return $this->get(key: 'routes', callback: $callback);
    }

    public function getPatternWithStopsAndTrips(int $patternId, callable $callback): PatternWithStopsAndTripsDTO
    {
        return $this->get(key: "pattern.$patternId", callback: $callback);
    }

    public function getRoute(int $routeId, callable $callback): RouteWithPatternsDTO
    {
        return $this->get(key: "route.$routeId", callback: $callback);
    }

    public function invalidate(): void
    {
        $this->clear();
    }
}
