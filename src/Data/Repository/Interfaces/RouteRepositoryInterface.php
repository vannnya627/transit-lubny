<?php

declare(strict_types=1);

namespace App\Data\Repository\Interfaces;

use App\Data\Entity\Route\Pattern;
use App\Data\Entity\Route\Route;

interface RouteRepositoryInterface
{
    public function getRouteById(int $id): Route;

    /**
     * @return list<Route>
     */
    public function getAllRoutes(): array;

    public function getPatternById(int $patternId): Pattern;

    /**
     * @param array<int, int> $patternIds
     *
     * @return list<Pattern>
     */
    public function findPatternsWithRoutesByIds(array $patternIds): array;

    public function getPatternWithRouteById(int $patternId): Pattern;
}
