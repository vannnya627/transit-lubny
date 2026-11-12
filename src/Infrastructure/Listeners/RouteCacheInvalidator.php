<?php

declare(strict_types=1);

namespace App\Infrastructure\Listeners;

use App\Application\Events\RouteImportCompletedEvent;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: RouteImportCompletedEvent::class, method: 'invalidate')]
final readonly class RouteCacheInvalidator
{
    public function __construct(
        #[Target('route.cache')]
        private CacheItemPoolInterface $routeCache,
        #[Target('stop.cache')]
        private CacheItemPoolInterface $stopCache,
        #[Target('trip.cache')]
        private CacheItemPoolInterface $tripCache,
        #[Target('departure.cache')]
        private CacheItemPoolInterface $departureCache,
    ) {}

    public function invalidate(): void
    {
        $this->routeCache->clear();
        $this->stopCache->clear();
        $this->tripCache->clear();
        $this->departureCache->clear();
    }
}
