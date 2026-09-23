<?php

declare(strict_types=1);

namespace App\UI\API\Transformers;

use App\DTOs\PatternDTO;
use App\UI\API\Responses\PatternResponse;

final readonly class PatternTransformer
{
    public function transformer(PatternDTO $patternDTO): PatternResponse
    {
        return new PatternResponse(
            id: $patternDTO->id,
            direction: $patternDTO->direction->getLabel(),
            name: $patternDTO->name,
        );
    }
}
