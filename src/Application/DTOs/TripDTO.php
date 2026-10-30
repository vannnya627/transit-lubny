<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Entity\Trip\Trip;
use App\Domain\ValueObjects\ServiceDays;

final readonly class TripDTO
{
    /**
     * @param array<int, string> $stopTimes
     */
    private function __construct(
        public int $tripId,
        public ServiceDays $serviceDays,
        public array $stopTimes,
    ) {}

    /**
     * @param array<int,string> $stopTimeStr
     */
    public static function create(Trip $trip, array $stopTimeStr): TripDTO
    {
        return new self(
            tripId: $trip->id,
            serviceDays: $trip->serviceDays,
            stopTimes: $stopTimeStr,
        );
    }
}
