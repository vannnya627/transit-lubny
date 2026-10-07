<?php

declare(strict_types=1);

namespace App\Caching\Interfaces;

interface CacheInterface
{
    /**
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    public function get(string $key, int $ttlMinutes, callable $callback): mixed;
}
