<?php

declare(strict_types=1);

namespace App\Application\DTOs\Inputs;

use App\Domain\ValueObjects\TimeOfDay;

final readonly class GetTripInput
{
    public function __construct(
        public int $tripId,
        public TimeOfDay $time,
    ) {}
}
