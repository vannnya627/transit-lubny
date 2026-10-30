<?php

declare(strict_types=1);

namespace App\Application\Tasks\Route;

use App\Domain\Entity\Route\Route;
use App\Domain\Repositories\RouteRepositoryInterface;

readonly class GetAllRouteTask
{
    public function __construct(
        private RouteRepositoryInterface $repository,
    ) {}

    /**
     * @return list<Route>
     */
    public function run(): array
    {
        return $this->repository->getAllRoutes();
    }
}
