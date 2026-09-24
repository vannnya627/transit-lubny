<?php

declare(strict_types=1);

namespace App\UI\API\Responses;

final readonly class StopResponse
{
    public function __construct(
        public int $sequence,
        public int $stopId,
        public string $name,
    ) {}
}
