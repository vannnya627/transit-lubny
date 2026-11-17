<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class StopWithRoutesDTO
{
    /**
     * @param list<RouteDTO> $routesDTO
     */
    private function __construct(
        public StopDTO $stopDTO,
        public array $routesDTO,
    ) {}

    /**
     * @param list<RouteDTO> $routesDTO
     */
    public static function create(StopDTO $stopDTO, array $routesDTO): self
    {
        return new self(stopDTO: $stopDTO, routesDTO: $routesDTO);
    }
}
