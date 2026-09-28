<?php

declare(strict_types=1);

namespace App\UI\API\Responses;

final readonly class StopForTripResponse
{
    public function __construct(
        public int $sequence,
        public StopResponse $stop,
        public string $arrivalTime,
        public bool $isPassed,
    ) {}

}
