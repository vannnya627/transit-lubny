<?php

declare(strict_types=1);

namespace App\UI\API\Transformers;

use App\DTOs\TripDTO;
use App\UI\API\Responses\TripResponse;

final readonly class TripTransformer
{
    public function transform(TripDTO $dto): TripResponse
    {
        return new TripResponse(
            tripId: $dto->tripId,
            serviceDays: $dto->serviceDays,
            stopTimes: $dto->stopTimes,
        );
    }
}
