<?php

declare(strict_types=1);

namespace App\Infrastructure\Listeners;

use App\Application\Caching\DepartureCacheInterface;
use App\Application\Caching\RouteCacheInterface;
use App\Application\Caching\StopCacheInterface;
use App\Application\Caching\TripCacheInterface;
use App\Application\Events\RouteImportCompletedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: RouteImportCompletedEvent::class, method: 'invalidate')]
final readonly class RouteCacheInvalidator
{
    public function __construct(
        private RouteCacheInterface $routeCache,
        private StopCacheInterface $stopCache,
        private TripCacheInterface $tripCache,
        private DepartureCacheInterface $departureCache,
    ) {}

    public function invalidate(): void
    {
        $this->routeCache->invalidate();
        $this->stopCache->invalidate();
        $this->tripCache->invalidate();
        $this->departureCache->invalidate();
    }
}
