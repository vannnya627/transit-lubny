<?php

declare(strict_types=1);

namespace App\DTOs\Input;

use DateTimeImmutable;
use DateTimeZone;

final readonly class GetStopDeparturesInput
{
    public const int DEFAULT_LIMIT = 10;
    public int $limit;
    public DateTimeImmutable $time;

    public function __construct(
        public int $stopId,
        ?DateTimeImmutable $time = null,
        ?int $limit = null,
    ) {
        $this->limit = $limit ?? self::DEFAULT_LIMIT;
        $this->time = $time ?? new DateTimeImmutable('now', new DateTimeZone('Europe/Kyiv'));
    }

    public function getSecondsFromMidnight(): int
    {
        return ((int) $this->time->format('H') * 3600) +
            ((int) $this->time->format('i') * 60) +
            ((int) $this->time->format('s'));
    }
}
