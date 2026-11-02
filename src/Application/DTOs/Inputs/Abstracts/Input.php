<?php

declare(strict_types=1);

namespace App\Application\DTOs\Inputs\Abstracts;

use App\Domain\ValueObjects\TimeOfDay;
use DateTimeImmutable;

abstract readonly class Input
{
    public TimeOfDay $time;

    protected function __construct(?DateTimeImmutable $time = null)
    {
        $this->time = null !== $time
            ? TimeOfDay::fromDateTime($time)
            : TimeOfDay::now();
    }
}
