<?php

declare(strict_types=1);

namespace App\Application\Import\DTOs;

use App\Domain\ValueObjects\Coordinates;

final readonly class StopImportData
{
    public function __construct(
        public int $cityCode,
        public string $name,
        public Coordinates $coordinates,
    ) {}
}
