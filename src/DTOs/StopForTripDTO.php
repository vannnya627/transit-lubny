<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Data\Entity\Trip\StopTime;

final readonly class StopForTripDTO
{
    private function __construct(
        public int $sequence,
        public StopDTO $stop,
        public string $arrivalTime,
        public int $arrivalTimeInSeconds,
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

    public function withIsPassed(int $secondsFromMidnight): self
    {
        return new self(
            sequence: $this->sequence,
            stop: $this->stop,
            arrivalTime: $this->arrivalTime,
            arrivalTimeInSeconds: $this->arrivalTimeInSeconds,
            isPassed: $this->arrivalTimeInSeconds < $secondsFromMidnight,
        );
    }
}
