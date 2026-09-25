<?php

declare(strict_types=1);

namespace App\Tasks\Route;

use App\Entity\Route\Pattern;
use App\Repository\Interfaces\RouteRepositoryInterface;

final readonly class FindPatternsWithRoutesByIdsTask
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
