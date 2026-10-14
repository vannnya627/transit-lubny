<?php

declare(strict_types=1);

namespace App\DTOs\Input;

use App\DTOs\Input\Abstracts\Input;
use DateTimeImmutable;

final readonly class GetStopDeparturesInput extends Input
{
    public const int DEFAULT_LIMIT = 10;
    public int $limit;

    public function __construct(
        public int $stopId,
        ?DateTimeImmutable $time = null,
        ?int $limit = null,
    ) {
        $this->limit = $limit ?? self::DEFAULT_LIMIT;
        parent::__construct($time);
    }
}
