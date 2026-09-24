<?php

declare(strict_types=1);

namespace App\Repository\Interfaces;

use App\Entity\Route\Pattern;
use App\Entity\Route\Route;

interface RouteRepositoryInterface
{
    public function getRouteById(int $id): Route;

    /**
     * @return list<Route>
     */
    public function getAllRoutes(): array;

    public function getPatternById(int $patternId): Pattern;
}
