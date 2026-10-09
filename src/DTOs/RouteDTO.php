<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Data\Entity\Route\Route;

final readonly class RouteDTO
{
    private function __construct(
        public int $id,
        public string $shortName,
        public string $longName,
    ) {}

    public static function create(Route $route): self
    {
        return new self(
            id: $route->id,
            shortName: $route->shortName,
            longName: $route->longName,
        );
    }
}
