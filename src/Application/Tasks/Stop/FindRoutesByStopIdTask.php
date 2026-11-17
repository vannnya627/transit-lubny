<?php

declare(strict_types=1);

namespace App\Application\Tasks\Stop;

use App\Domain\Entity\Route\Route;
use App\Domain\Repositories\Route\RouteRepositoryInterface;

readonly class FindRoutesByStopIdTask
{
    public function __construct(
        private RouteRepositoryInterface $repository,
    ) {}

    /**
     * @return list<Route>
     */
    public function run(int $stopId): array
    {
        return $this->repository->findRoutesByStopId($stopId);
    }
}
