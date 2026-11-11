<?php

declare(strict_types=1);

namespace App\Application\DTOs\Inputs;

use App\Domain\ValueObjects\Coordinates;

final readonly class SearchStopsInput
{
    public const int DEFAULT_RADIUS = 500;
    public int $radius;
    public ?string $q;

    public function __construct(
        ?string $q = null,
        public ?Coordinates $coordinates = null,
        ?int $radius = null,
    ) {
        $this->radius = $radius ?? self::DEFAULT_RADIUS;
        $this->q = $this->normalizeQuery($q);
    }

    private function normalizeQuery(?string $query): ?string
    {
        if (null === $query) {
            return null;
        }

        $query = mb_strtolower(trim($query));

        return '' === $query ? null : $query;
    }
}
