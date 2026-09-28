<?php

declare(strict_types=1);

namespace App\Tasks\Route;

use App\Data\Entity\Route\Route;
use App\Data\Repository\Interfaces\RouteRepositoryInterface;

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
