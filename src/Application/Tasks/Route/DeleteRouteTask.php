<?php

declare(strict_types=1);

namespace App\Application\Tasks\Route;

use App\Application\Tasks\Trip\FindTripsByPatternIdsTask;
use App\Domain\Entity\Route\Route;
use App\Domain\Repositories\Route\RouteRepositoryInterface;
use App\Domain\Repositories\Trip\TripRepositoryInterface;

readonly class DeleteRouteTask
{
    public function __construct(
        private FindTripsByPatternIdsTask $findTripsByPatternIdsTask,
        private TripRepositoryInterface $tripRepository,
        private RouteRepositoryInterface $routeRepository,
    ) {}

    public function run(Route $route): void
    {
        $patternsId = array_column($route->patterns->getValues(), 'id');
        $trips = $this->findTripsByPatternIdsTask->run($patternsId);

        foreach ($trips as $trip) {
            $this->tripRepository->remove($trip);
        }

        $this->routeRepository->remove($route);
    }
}
