<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class RouteWithPatternsDTO
{
    /**
     * @param list<PatternDTO> $parameters
     */
    public function __construct(
        public int $id,
        public string $shortName,
        public string $longName,
        public array $parameters,
    ) {}
}
