<?php

declare(strict_types=1);

namespace App\UI\API\Transformers;

use App\DTOs\StopForScheduleDTO;
use App\UI\API\Responses\StopForScheduleResponse;

final readonly class StopForScheduleTransformer
{
    public function transform(StopForScheduleDTO $dto): StopForScheduleResponse
    {
        return new StopForScheduleResponse(
            sequence: $dto->sequence,
            stopId: $dto->stopId,
            name: $dto->name,
        );
    }
}
