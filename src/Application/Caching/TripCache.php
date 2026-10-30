<?php

declare(strict_types=1);

namespace App\Application\Caching;

use App\Application\Caching\Abstracts\Cache;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\Cache\CacheInterface;

readonly class TripCache extends Cache
{
    public function __construct(#[Target(name: 'trip.cache')] CacheInterface $cache)
    {
        parent::__construct($cache);
    }
}
