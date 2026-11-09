<?php

declare(strict_types=1);

namespace App\Application\Actions\Stop;

use App\Application\Caching\DepartureCache;
use App\Application\DTOs\DepartureDTO;
use App\Application\DTOs\Inputs\GetStopDeparturesInput;
use App\Application\DTOs\RouteDTO;
use App\Application\DTOs\StopWithDeparturesDTO;
use App\Application\Tasks\Pattern\FindPatternsWithRoutesByIdsTask;
use App\Application\Tasks\Stop\GetStopByIdTask;
use App\Application\Tasks\StopTime\FindStopTimesWithTripTask;
use App\Domain\Entity\Trip\StopTime;

final readonly class GetStopDeparturesAction
{
    public function __construct(
        private FindPatternsWithRoutesByIdsTask $findPatternsWithRoutesByIds,
        private GetStopByIdTask $getStopByIdTask,
        private DepartureCache $cache,
        private FindStopTimesWithTripTask $findStopTimesWithTripTask,
    ) {}

    public function run(GetStopDeparturesInput $input): StopWithDeparturesDTO
    {

        return $this->cache->get(
            key: "stop.$input->stopId.time.{$input->time->value}",
            callback: function () use ($input) {

                // todo - тут знаходжу зупинку щоб взяти тільки з неї імя, можливо змінити
                $stop = $this->getStopByIdTask->run($input->stopId);

                $stopTimes = $this->findStopTimesWithTripTask->run($input);

                $patternIds = $this->extractPatternIds($stopTimes);
                $patterns = $this->findPatternsWithRoutesByIds->run($patternIds);

                $patternsById = array_column($patterns, null, 'id');

                $departures = array_map(function (StopTime $stopTime) use ($input, $patternsById): DepartureDTO {
                    $pattern = $patternsById[$stopTime->trip->patternId];

                    return DepartureDTO::create(
                        pattern: $pattern,
                        route: RouteDTO::create($pattern->route),
                        currentTime: $input->time,
                        stopTime: $stopTime,
                    );
                }, $stopTimes);

                return StopWithDeparturesDTO::create(stop: $stop, departures: $departures);
            },
        );
    }

    /**
     * @param list<StopTime> $stopTimes
     *
     * @return array<int, int>
     */
    private function extractPatternIds(array $stopTimes): array
    {
        return array_map(fn(StopTime $stopTime) => $stopTime->trip->patternId, $stopTimes)
                |> array_unique(...)
                |> array_values(...);
    }
}
