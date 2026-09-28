<?php

declare(strict_types=1);

namespace App\Actions\Stop;

use App\DTOs\Input\SearchStopsInput;
use App\DTOs\StopDTO;
use App\Entity\Stop;
use App\Tasks\Stop\FindStopsBySearchQueryTask;

final readonly class GetStopsAction
{
    public function __construct(
        private FindStopsBySearchQueryTask $findStopsBySearchQueryTask,
    ) {}

    /**
     * @return list<StopDTO>
     */
    public function run(SearchStopsInput $input): array
    {
        $stops = $this->findStopsBySearchQueryTask->run($input);

        return array_map(fn(Stop $stop) => new StopDTO(
            id: $stop->id,
            name: $stop->name,
            coordinates: $stop->coordinates,
            cityCode: $stop->cityCode,
        ), $stops);
    }
}
