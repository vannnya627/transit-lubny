<?php

declare(strict_types=1);

namespace App\UI\API\Transformers;

use App\DTOs\StopForTripDTO;
use App\UI\API\Responses\StopForTripResponse;

final readonly class StopForTripTransformer
{
    public function __construct(
        private StopTransformer $stopTransformer,
    ) {}

    public function transform(StopForTripDTO $stopForTripDTO): StopForTripResponse
    {
        return new StopForTripResponse(
            sequence: $stopForTripDTO->sequence,
            stop: $this->stopTransformer->transform($stopForTripDTO->stop),
            arrivalTime: $stopForTripDTO->arrivalTime,
            isPassed: $stopForTripDTO->isPassed,
        );
    }
}
