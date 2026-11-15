<?php

declare(strict_types=1);

namespace App\Infrastructure\Caches\Abstracts;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

abstract readonly class Cache
{
    protected function __construct(
        private CacheInterface $cache,
        private CacheItemPoolInterface $cacheItemPool,
    ) {}

    /**
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    protected function get(string $key, callable $callback): mixed
    {
        try {
            return $this->cache->get($key, fn() => $callback());
        } catch (InvalidArgumentException) {
            return $callback();
        }
    }

    protected function clear(): void
    {
        $this->cacheItemPool->clear();
    }
}
