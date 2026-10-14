<?php

declare(strict_types=1);

namespace App\Caching;

use App\Caching\Abstracts\Cache;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\Cache\CacheInterface;

readonly class DepartureCache extends Cache
{
    public function __construct(#[Target(name: 'departure.cache')] CacheInterface $cache)
    {
        parent::__construct($cache);
    }
}
