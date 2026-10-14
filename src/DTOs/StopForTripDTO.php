<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Data\Entity\Trip\StopTime;

final class StopForTripDTO
{
    private function __construct(
        public readonly int $sequence,
        public readonly StopDTO $stop,
        public readonly string $arrivalTime,
        public readonly int $arrivalTimeInSeconds,
        public bool $isPassed = false,
    ) {}

    public static function create(StopDTO $stop, StopTime $stopTime): self
    {
        return new self(
            sequence: $stopTime->stopSequence,
            stop: $stop,
            arrivalTime: $stopTime->getFormattedArrivalTime(),
            arrivalTimeInSeconds: $stopTime->arrivalTime,
        );
    }

    public function checkIsPassed(int $secondsFromMidnight): void
    {
        if ($this->arrivalTimeInSeconds < $secondsFromMidnight) {
            $this->isPassed = true;
        }
    }
}
