<?php

declare(strict_types=1);

namespace App\Actions\Stop;

use App\Caching\StopCache;
use App\DTOs\Input\SearchStopsInput;
use App\DTOs\StopDTO;
use App\Tasks\Stop\FindStopsBySearchQueryTask;

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
            key: 'stops',
            callback: function () use ($input) {
                $stops = $this->findStopsBySearchQueryTask->run($input);

                return array_map(StopDTO::create(...), $stops);
            },
        );
    }
}
