<?php

declare(strict_types=1);

namespace App\Application\Import\DTOs;

use App\Domain\ValueObjects\TimeOfDay;

final readonly class TripImportData
{
    /**
     * A null time means the stop has no scheduled arrival time on this trip.
     *
     * @param list<TimeOfDay|null> $times
     */
    public function __construct(
        public array $times,
    ) {}
}
