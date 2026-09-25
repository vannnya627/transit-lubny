<?php

declare(strict_types=1);

namespace App\DTOs\Input;

use App\ValueObjects\Coordinates;

final readonly class SearchStopsInput
{
    public const int DEFAULT_RADIUS = 500;
    public int $radius;

    public function __construct(
        public ?string $q = null,
        public ?Coordinates $coordinates = null,
        ?int $radius = null,
    ) {
        $this->radius = $radius ?? self::DEFAULT_RADIUS;
    }
}
