<?php

declare(strict_types=1);

namespace App\Application\Actions\Stop;

use App\Application\Caching\Keys\SearchStopsCacheKey;
use App\Application\Caching\StopCacheInterface;
use App\Application\DTOs\Inputs\SearchStopsInput;
use App\Application\DTOs\StopDTO;
use App\Application\Tasks\Stop\FindStopsBySearchQueryTask;

final readonly class GetStopsAction
{
    public function __construct(
        private FindStopsBySearchQueryTask $findStopsBySearchQueryTask,
        private StopCacheInterface $cache,
    ) {}

    /**
     * @return list<StopDTO>
     */
    public function run(SearchStopsInput $input): array
    {

        return $this->cache->getStops(
            key: SearchStopsCacheKey::fromInput($input),
            callback: function () use ($input) {
                $stops = $this->findStopsBySearchQueryTask->run($input);

                return array_map(StopDTO::create(...), $stops);
            },
        );
    }
}
