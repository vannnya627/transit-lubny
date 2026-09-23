<?php

declare(strict_types=1);

namespace App\Tasks\Route;

use App\Entity\Route\Route;
use App\Repository\Interfaces\RouteRepositoryInterface;

final readonly class GetRouteTask
{
    public function __construct(
        private RouteRepositoryInterface $repository,
    ) {}

    public function run(int $routeId): Route
    {
        return $this->repository->getRouteById($routeId);
    }
}
