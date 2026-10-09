<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Data\Entity\Route\Route;

final readonly class RouteWithPatternsDTO
{
    /**
     * @param list<PatternDTO> $patterns
     */
    private function __construct(
        public int $id,
        public string $shortName,
        public string $longName,
        public array $patterns,
    ) {}

    /**
     * @param list<PatternDTO> $patterns
     */
    public static function create(Route $route, array $patterns): self
    {
        return new self(
            id: $route->id,
            shortName: $route->shortName,
            longName: $route->longName,
            patterns: $patterns,
        );
    }
}
