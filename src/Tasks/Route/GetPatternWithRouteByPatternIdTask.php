<?php

declare(strict_types=1);

namespace App\Tasks\Route;

use App\Data\Entity\Route\Pattern;
use App\Data\Repository\Interfaces\RouteRepositoryInterface;

final readonly class GetPatternWithRouteByPatternIdTask
{
    public function __construct(
        public RouteRepositoryInterface $routeRepository,
    ) {}

    public function run(int $patternId): Pattern
    {
        return  $this->routeRepository->getPatternWithRouteById($patternId);
    }
}
