<?php

declare(strict_types=1);

namespace App\Presentation\UI\API\Transformers;

use App\Application\DTOs\StopDTO;
use App\Presentation\UI\API\Responses\StopResponse;

final readonly class StopTransformer
{
    public function transform(StopDTO $dto): StopResponse
    {
        return new StopResponse(
            id: $dto->id,
            name: $dto->name,
            lat: $dto->coordinates->lat,
            lon: $dto->coordinates->lon,
            cityCode: $dto->cityCode,
        );
    }
}
