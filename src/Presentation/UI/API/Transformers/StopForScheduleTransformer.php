<?php

declare(strict_types=1);

namespace App\Presentation\UI\API\Transformers;

use App\Application\DTOs\StopForScheduleDTO;
use App\Presentation\UI\API\Responses\StopForScheduleResponse;

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
