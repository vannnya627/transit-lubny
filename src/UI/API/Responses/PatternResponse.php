<?php

declare(strict_types=1);

namespace App\UI\API\Responses;

final readonly class PatternResponse
{
    public function __construct(
        public int $id,
        public string $direction,
        public string $name,
    ) {}
}
