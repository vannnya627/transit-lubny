<?php

declare(strict_types=1);

namespace App\Caching\Abstracts;

use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

abstract readonly class Cache
{
    public function __construct(
        private CacheInterface $cache,
    ) {}

    /**
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    public function get(string $key, callable $callback): mixed
    {
        try {
            return $this->cache->get($key, fn() => $callback());
        } catch (InvalidArgumentException $e) {
            return $callback();
        }
    }
}
