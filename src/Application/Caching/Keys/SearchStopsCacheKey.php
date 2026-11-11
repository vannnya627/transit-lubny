<?php

declare(strict_types=1);

namespace App\Application\Caching\Keys;

use App\Application\DTOs\Inputs\SearchStopsInput;
use App\Domain\ValueObjects\Coordinates;

final readonly class SearchStopsCacheKey
{
    private function __construct(
        private ?string $query,
        private ?Coordinates $coordinates,
        private ?int $radius,
    ) {}

    public static function fromInput(SearchStopsInput $input): self
    {
        return new self(
            query: $input->q,
            coordinates: $input->coordinates,
            radius: $input->radius,
        );
    }

    public function toString(): string
    {
        $queryPart = null === $this->query ? 'all' : hash('xxh3', $this->query);

        if (null === $this->coordinates) {
            return sprintf('stops.v1.q.%s', $queryPart);
        }

        return sprintf(
            'stops.v1.geo.%s.%s.%d.%s',
            var_export($this->coordinates->lat, true),
            var_export($this->coordinates->lon, true),
            $this->radius,
            $queryPart,
        );
    }
}
