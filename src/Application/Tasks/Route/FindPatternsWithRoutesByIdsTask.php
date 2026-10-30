<?php

declare(strict_types=1);

namespace App\Application\Tasks\Route;

use App\Domain\Entity\Route\Pattern;
use App\Domain\Repositories\RouteRepositoryInterface;

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
