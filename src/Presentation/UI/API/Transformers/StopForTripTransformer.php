<?php

declare(strict_types=1);

namespace App\Presentation\UI\API\Transformers;

use App\Application\DTOs\StopForTripDTO;
use App\Presentation\UI\API\Responses\StopForTripResponse;

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
