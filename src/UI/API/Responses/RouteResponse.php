<?php

declare(strict_types=1);

namespace App\UI\API\Responses;

use App\DTOs\RouteDTO;

final readonly class RouteResponse
{
    private function __construct(
        public int $id,
        public string $shortName,
        public string $longName,
    ) {}

    public static function create(RouteDTO $dto): self
    {
        return new self($dto->id, $dto->shortName, $dto->longName);
    }
}
