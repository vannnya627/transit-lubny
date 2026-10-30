<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entity\Route\Pattern;
use App\Domain\Entity\Route\Route;

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

    public function remove(Route $route): void;

    public function commit(): void;

    public function saveAndCommit(Route $route): void;

    public function findRouteByShortName(string $shortName): ?Route;
}
