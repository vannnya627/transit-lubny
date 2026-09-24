<?php

declare(strict_types=1);

namespace App\UI\API\Transformers;

use App\DTOs\StopDTO;
use App\UI\API\Responses\StopResponse;

final readonly class StopTransformer
{
    public function transform(StopDTO $dto): StopResponse
    {
        return new StopResponse(
            sequence: $dto->sequence,
            stopId: $dto->stopId,
            name: $dto->name,
        );
    }
}
