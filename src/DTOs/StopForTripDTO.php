<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Data\Entity\Stop;
use App\Data\Entity\Trip\StopTime;

final readonly class StopForTripDTO
{
    private function __construct(
        public int $sequence,
        public StopDTO $stop,
        public string $arrivalTime,
        public bool $isPassed,
    ) {}

    public static function create(Stop $stop, StopTime $stopTime, int $secondsFromMidnight): self
    {
        return new self(
            sequence: $stopTime->stopSequence,
            stop: StopDTO::create($stop),
            arrivalTime: $stopTime->getFormattedArrivalTime(),
            isPassed: $stopTime->isPassed($secondsFromMidnight),
        );
    }
}
