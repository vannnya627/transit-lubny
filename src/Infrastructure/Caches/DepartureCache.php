<?php

declare(strict_types=1);

namespace App\Infrastructure\Caches;

use App\Application\Caching\DepartureCacheInterface;
use App\Application\Caching\Keys\GetStopDeparturesCacheKey;
use App\Application\DTOs\StopWithDeparturesDTO;
use App\Infrastructure\Caches\Abstracts\Cache;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\Cache\CacheInterface;

readonly class DepartureCache extends Cache implements DepartureCacheInterface
{
    public function __construct(
        #[Target(name: 'departure.cache')]
        CacheInterface $cache,
        #[Target(name: 'departure.cache')]
        CacheItemPoolInterface $cacheItemPool,
    ) {
        parent::__construct($cache, $cacheItemPool);
    }

    public function getStopDepartures(GetStopDeparturesCacheKey $key, callable $callback): StopWithDeparturesDTO
    {
        return $this->get($key->toString(), $callback);
    }

    public function invalidate(): void
    {
        $this->clear();
    }
}
