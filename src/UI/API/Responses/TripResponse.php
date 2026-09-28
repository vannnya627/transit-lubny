<?php

declare(strict_types=1);

namespace App\UI\API\Responses;

use App\Data\ValueObjects\ServiceDays;

final readonly class TripResponse
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
