<?php

declare(strict_types=1);

namespace App\DTOs\Input;

use App\DTOs\Input\Abstracts\Input;

final readonly class GetTripInput extends Input
{
    public function __construct(
        public int $tripId,
    ) {
        parent::__construct();
    }
}
