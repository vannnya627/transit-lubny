<?php

declare(strict_types=1);

namespace App\Application\Tasks\Route;

use App\Domain\Entity\Route\Pattern;
use App\Domain\Repositories\RouteRepositoryInterface;

readonly class GetPatternWithRouteByPatternIdTask
{
    public function __construct(
        public RouteRepositoryInterface $routeRepository,
    ) {}

    public function run(int $patternId): Pattern
    {
        return  $this->routeRepository->getPatternWithRouteById($patternId);
    }
}
