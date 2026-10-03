<?php

declare(strict_types=1);

namespace App\UI\API\Responses;

final readonly class StopResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public float $lat,
        public float $lon,
        public int $cityCode,
    ) {}
}
