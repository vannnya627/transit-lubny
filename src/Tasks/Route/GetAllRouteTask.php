<?php

declare(strict_types=1);

namespace App\Tasks\Route;

use App\Entity\Route\Route;
use App\Repository\Interfaces\RouteRepositoryInterface;

final readonly class GetAllRouteTask
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
