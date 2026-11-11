<?php

declare(strict_types=1);

namespace App\Application\Caching\Keys;

use App\Application\DTOs\Inputs\GetStopDeparturesInput;
use App\Domain\ValueObjects\TimeOfDay;

final readonly class GetStopDeparturesCacheKey
{
    private function __construct(
        private int $stopId,
        private TimeOfDay $time,
        private int $limit,
    ) {}

    public static function fromInput(GetStopDeparturesInput $input): self
    {
        return new self(
            stopId: $input->stopId,
            time: $input->time,
            limit: $input->limit,
        );
    }

    public function toString(): string
    {
        return sprintf(
            'departures.v1.stop.%d.time.%d.limit.%d',
            $this->stopId,
            intdiv($this->time->value, 60) * 60,
            $this->limit,
        );
    }
}
