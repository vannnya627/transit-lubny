<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class StopForTripDTO
{
    public function __construct(
        public int $sequence,
        public StopDTO $stop,
        public string $arrivalTime,
        public bool $isPassed,
    ) {}
}
