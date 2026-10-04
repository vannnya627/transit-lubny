<?php

declare(strict_types=1);

namespace App\Tasks\Route;

use App\Data\Entity\Route\Route;
use App\Data\Repository\Interfaces\RouteRepositoryInterface;

readonly class GetRouteTask
{
    public function __construct(
        private RouteRepositoryInterface $repository,
    ) {}

    public function run(int $routeId): Route
    {
        return $this->repository->getRouteById($routeId);
    }
}
