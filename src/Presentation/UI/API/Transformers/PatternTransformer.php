<?php

declare(strict_types=1);

namespace App\Presentation\UI\API\Transformers;

use App\Application\DTOs\PatternDTO;
use App\Presentation\UI\API\Responses\PatternResponse;

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
