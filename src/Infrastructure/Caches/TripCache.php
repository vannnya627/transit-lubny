<?php

declare(strict_types=1);

namespace App\Infrastructure\Caches;

use App\Application\Caching\TripCacheInterface;
use App\Application\DTOs\TripDetailsDTO;
use App\Infrastructure\Caches\Abstracts\Cache;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\Cache\CacheInterface;

readonly class TripCache extends Cache implements TripCacheInterface
{
    public function __construct(
        #[Target(name: 'trip.cache')]
        CacheInterface $cache,
        #[Target(name: 'trip.cache')]
        CacheItemPoolInterface $cacheItemPool,
    ) {
        parent::__construct($cache, $cacheItemPool);
    }

    public function getTrip(int $tripId, callable $callback): TripDetailsDTO
    {
        return  $this->get(key: "trip.$tripId", callback: $callback);
    }

    public function invalidate(): void
    {
        $this->clear();
    }
}
