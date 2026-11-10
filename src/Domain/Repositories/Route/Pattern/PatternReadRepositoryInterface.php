<?php

declare(strict_types=1);

namespace App\Domain\Repositories\Route\Pattern;

use App\Domain\Entity\Route\Pattern\Pattern;

interface PatternReadRepositoryInterface
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
