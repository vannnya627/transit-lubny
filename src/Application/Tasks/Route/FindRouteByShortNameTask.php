<?php

declare(strict_types=1);

namespace App\Application\Tasks\Route;

use App\Domain\Entity\Route\Route;
use App\Domain\Repositories\RouteRepositoryInterface;

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
