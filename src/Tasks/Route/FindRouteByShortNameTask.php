<?php

declare(strict_types=1);

namespace App\Tasks\Route;

use App\Data\Entity\Route\Route;
use App\Data\Repository\Interfaces\RouteRepositoryInterface;

readonly class FindRouteByShortNameTask
{
    public function __construct(
        private RouteRepositoryInterface $repository,
    ) {}

    public function run(string $shortName): ?Route
    {
        return $this->repository->findRouteByShortName($shortName);
    }
}
