<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Data\Entity\Route\PatternStop;
use App\Data\Entity\Stop;

final readonly class StopForScheduleDTO
{
    private function __construct(
        public int $sequence,
        public int $stopId,
        public string $name,
    ) {}

    public static function create(Stop $stop, PatternStop $patternStop): self
    {
        return new self(
            sequence: $patternStop->stopSequence,
            stopId: $stop->id,
            name: $stop->name,
        );
    }
}
