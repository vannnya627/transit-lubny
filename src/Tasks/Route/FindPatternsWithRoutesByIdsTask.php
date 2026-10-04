<?php

declare(strict_types=1);

namespace App\Tasks\Route;

use App\Data\Entity\Route\Pattern;
use App\Data\Repository\Interfaces\RouteRepositoryInterface;

readonly class FindPatternsWithRoutesByIdsTask
{
    public function __construct(
        private RouteRepositoryInterface $repository,
    ) {}

    /**
     * @param array<int, int> $patternIds
     *
     * @return list<Pattern>
     */
    public function run(array $patternIds): array
    {
        return $this->repository->findPatternsWithRoutesByIds($patternIds);
    }
}
