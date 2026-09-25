<?php

declare(strict_types=1);

namespace App\Actions\Stop;

use App\DTOs\StopDTO;
use App\Tasks\Stop\GetStopByIdTask;

final readonly class GetStopAction
{
    public function __construct(
        private GetStopByIdTask $getStopByIdTask,
    ) {}

    public function run(int $stopId): StopDTO
    {
        $stop = $this->getStopByIdTask->run($stopId);

        return new StopDTO(
            id: $stop->id,
            name: $stop->name,
            coordinates: $stop->coordinates,
        );
    }
}
