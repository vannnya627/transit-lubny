<?php

declare(strict_types=1);

namespace App\Presentation\UI\API\Transformers;

use App\Application\DTOs\PatternWithStopsAndTripsDTO;
use App\Presentation\UI\API\Responses\PatternWithStopsAndTripsResponse;

final readonly class PatternWithStopsAndTripsTransformer
{
    public function __construct(
        private StopForScheduleTransformer $stopForScheduleTransformer,
        private TripTransformer $tripTransformer,
    ) {}

    public function transform(PatternWithStopsAndTripsDTO $dto): PatternWithStopsAndTripsResponse
    {
        return new PatternWithStopsAndTripsResponse(
            id: $dto->id,
            direction: $dto->direction->getLabel(),
            name: $dto->name,
            stops: array_map($this->stopForScheduleTransformer->transform(...), $dto->stops),
            trips: array_map($this->tripTransformer->transform(...), $dto->trips),
        );
    }
}
