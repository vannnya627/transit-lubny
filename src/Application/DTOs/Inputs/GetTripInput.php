<?php

declare(strict_types=1);

namespace App\Application\DTOs\Inputs;

use App\Application\DTOs\Inputs\Abstracts\Input;

final readonly class GetTripInput extends Input
{
    public function __construct(
        public int $tripId,
    ) {
        parent::__construct();
    }
}
