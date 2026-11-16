<?php

declare(strict_types=1);

namespace App\Application\Import\DTOs;

final readonly class RouteImportData
{
    /**
     * @param list<PatternImportData> $patterns
     */
    public function __construct(
        public string $shortName,
        public string $longName,
        public array $patterns,
    ) {}
}
