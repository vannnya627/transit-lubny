<?php

declare(strict_types=1);

namespace App\Caching;

use App\Caching\Interfaces\CacheInterface as BaseCacheInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class RedisCache implements BaseCacheInterface
{
    public function __construct(
        private CacheInterface $cache,
    ) {}

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $key, int $ttlMinutes, callable $callback): mixed
    {
        return $this->cache->get($key, function (ItemInterface $item) use ($ttlMinutes, $callback) {

            $item->expiresAfter($ttlMinutes * 60);

            return $callback();
        });
    }
}
