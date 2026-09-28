<?php

declare(strict_types=1);

namespace App\Actions\Route;

use App\Data\Entity\Route\Pattern;
use App\DTOs\PatternDTO;
use App\DTOs\RouteWithPatternsDTO;
use App\Tasks\Route\GetRouteTask;

final readonly class GetRouteAction
{
    public function __construct(
        private GetRouteTask $getRouteTask,
    ) {}

    public function run(int $routeId): RouteWithPatternsDTO
    {
        $route = $this->getRouteTask->run($routeId);

        /** @var list<Pattern> $patterns */
        $patterns = array_values($route->patterns->toArray());

        $patternDTOs = array_map(fn(Pattern $pattern): PatternDTO => new PatternDTO(
            id: $pattern->id,
            direction: $pattern->direction,
            name: $pattern->name ?? '',
        ), $patterns);


        return new RouteWithPatternsDTO(
            id: $route->id,
            shortName: $route->shortName,
            longName: $route->longName,
            parameters: $patternDTOs,
        );
    }
}
