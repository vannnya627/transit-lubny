<?php

declare(strict_types=1);

namespace App\Application\DTOs\Inputs\Abstracts;

use App\Domain\ValueObjects\TimeOfDay;

abstract readonly class Input
{
    public TimeOfDay $time;

    protected function __construct(?TimeOfDay $time = null)
    {
        $this->time = $time ?? TimeOfDay::now();
    }
}
