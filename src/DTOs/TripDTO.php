<?php

declare(strict_types=1);

namespace App\DTOs;

use App\ValueObjects\ServiceDays;

final readonly class TripDTO
{
    /**
     * @param array<int, string> $stopTimes
     */
    public function __construct(
        public int $tripId,
        public ServiceDays $serviceDays,
        public array $stopTimes,
    ) {}
}
