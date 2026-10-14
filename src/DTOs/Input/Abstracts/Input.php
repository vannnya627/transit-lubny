<?php

declare(strict_types=1);

namespace App\DTOs\Input\Abstracts;

use DateTimeImmutable;
use DateTimeZone;

abstract readonly class Input
{
    protected DateTimeImmutable $time;

    protected function __construct(?DateTimeImmutable $time = null)
    {
        $this->time = $time ?? new DateTimeImmutable('now', new DateTimeZone('Europe/Kyiv'));
    }

    public function getSecondsFromMidnight(): int
    {
        return
            ((int) $this->time->format('H') * 3600) +
            ((int) $this->time->format('i') * 60) +
            ((int) $this->time->format('s'));
    }
}
