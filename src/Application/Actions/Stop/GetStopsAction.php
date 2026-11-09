<?php

declare(strict_types=1);

namespace App\Application\Actions\Stop;

use App\Application\Caching\StopCache;
use App\Application\DTOs\Inputs\SearchStopsInput;
use App\Application\DTOs\StopDTO;
use App\Application\Tasks\Stop\FindStopsBySearchQueryTask;

final readonly class GetStopsAction
{
    public function __construct(
        private FindStopsBySearchQueryTask $findStopsBySearchQueryTask,
        private StopCache $cache,
    ) {}

    /**
     * @return list<StopDTO>
     */
    public function run(SearchStopsInput $input): array
    {

        return $this->cache->get(
            key: "stops.$input->q",
            callback: function () use ($input) {
                $stops = $this->findStopsBySearchQueryTask->run($input);

                return array_map(StopDTO::create(...), $stops);
            },
        );
    }
}
