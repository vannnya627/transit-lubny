<?php

declare(strict_types=1);

namespace App\Application\Tasks\Pattern;

use App\Domain\Entity\Pattern\Pattern;
use App\Domain\Repositories\PatternRepositoryInterface;

readonly class FindPatternsWithRoutesByIdsTask
{
    public function __construct(
        private PatternRepositoryInterface $repository,
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
