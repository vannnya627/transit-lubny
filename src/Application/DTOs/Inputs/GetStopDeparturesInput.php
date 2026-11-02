<?php

declare(strict_types=1);

namespace App\Application\DTOs\Inputs;

use App\Domain\ValueObjects\TimeOfDay;

final readonly class GetStopDeparturesInput
{
    public const int DEFAULT_LIMIT = 10;
    public int $limit;

    public function __construct(
        public int $stopId,
        public TimeOfDay $time,
        ?int $limit = null,
    ) {
        $this->limit = $limit ?? self::DEFAULT_LIMIT;
    }
}
