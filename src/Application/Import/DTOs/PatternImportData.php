<?php

declare(strict_types=1);

namespace App\Application\Import\DTOs;

use App\Domain\Enums\Direction;

final readonly class PatternImportData
{
    /**
     * @param list<StopImportData> $stops
     * @param list<TripImportData> $trips
     */
    public function __construct(
        public Direction $direction,
        public string $name,
        public array $stops,
        public array $trips,
    ) {}
}
