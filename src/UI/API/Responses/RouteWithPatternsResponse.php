<?php

declare(strict_types=1);

namespace App\UI\API\Responses;

final readonly class RouteWithPatternsResponse
{
    /**
     * @param list<PatternResponse> $patterns
     */
    public function __construct(
        public int $id,
        public string $shortName,
        public string $longName,
        public array $patterns,
    ) {}
}
