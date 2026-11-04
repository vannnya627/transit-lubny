<?php

declare(strict_types=1);

namespace App\Infrastructure\Listeners;

use App\Domain\Entity\Route\Route;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;

#[AsEntityListener(event: 'prePersist', method: 'invalidate', entity: Route::class)]
#[AsEntityListener(event: 'preRemove', method: 'invalidate', entity: Route::class)]
#[AsEntityListener(event: 'preUpdate', method: 'invalidate', entity: Route::class)]
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
