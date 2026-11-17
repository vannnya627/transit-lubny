<?php

declare(strict_types=1);

namespace App\Infrastructure\Caches;

use App\Application\Caching\Keys\SearchStopsCacheKey;
use App\Application\Caching\StopCacheInterface;
use App\Application\DTOs\StopDTO;
use App\Application\DTOs\StopWithRoutesDTO;
use App\Infrastructure\Caches\Abstracts\Cache;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\Cache\CacheInterface;

readonly class StopCache extends Cache implements StopCacheInterface
{
    public function __construct(
        #[Target(name: 'stop.cache')]
        CacheInterface $cache,
        #[Target(name: 'stop.cache')]
        CacheItemPoolInterface $cacheItemPool,
    ) {
        parent::__construct($cache, $cacheItemPool);
    }

    public function getStop(int $stopId, callable $callback): StopDTO
    {
        return $this->get(key: "stop.$stopId", callback: $callback);
    }

    public function getStops(SearchStopsCacheKey $key, callable $callback): array
    {
        return $this->get($key->toString(), $callback);
    }

    public function invalidate(): void
    {
        $this->clear();
    }

    public function getStopWithRoutes(int $stopId, callable $callback): StopWithRoutesDTO
    {
        return $this->get(key: "stop.$stopId.routes", callback: $callback);
    }
}
