<?php

declare(strict_types=1);

namespace App\Presentation\UI\API\Transformers;

use App\Application\DTOs\TripDTO;
use App\Presentation\UI\API\Responses\TripResponse;

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
