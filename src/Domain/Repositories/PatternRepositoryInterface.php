<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entity\Pattern\Pattern;

interface PatternRepositoryInterface
{
    public function getPatternById(int $patternId): Pattern;

    /**
     * @param array<int, int> $patternIds
     *
     * @return list<Pattern>
     */
    public function findPatternsWithRoutesByIds(array $patternIds): array;

    public function getPatternWithRouteById(int $patternId): Pattern;
}
