<?php

declare(strict_types=1);

namespace App\Application\Actions\Stop;

use App\Application\Caching\DepartureCache;
use App\Application\DTOs\DepartureDTO;
use App\Application\DTOs\Inputs\GetStopDeparturesInput;
use App\Application\DTOs\RouteDTO;
use App\Application\DTOs\StopWithDeparturesDTO;
use App\Application\Tasks\Route\FindPatternsWithRoutesByIdsTask;
use App\Application\Tasks\Stop\GetStopByIdTask;
use App\Application\Tasks\Trip\FindTripsWithStopTimesForStopTask;
use App\Domain\Entity\Trip\Trip;

final readonly class GetStopDeparturesAction
{
    public function __construct(
        private FindTripsWithStopTimesForStopTask $findTripsWithStopTimesForStopTask,
        private FindPatternsWithRoutesByIdsTask $findPatternsWithRoutesByIds,
        private GetStopByIdTask $getStopByIdTask,
        private DepartureCache $cache,
    ) {}

    public function run(GetStopDeparturesInput $input): StopWithDeparturesDTO
    {

        return $this->cache->get(
            key: "stop.$input->stopId.time.{$input->time->value}",
            callback: function () use ($input) {

                // todo - тут знаходжу зупинку щоб взяти тільки з неї імя, можливо змінити
                $stop = $this->getStopByIdTask->run($input->stopId);
                $trips = $this->findTripsWithStopTimesForStopTask->run($input);

                $patternIds = $this->extractPatternIds($trips);
                $patterns = $this->findPatternsWithRoutesByIds->run($patternIds);

                $patternsById = array_column($patterns, null, 'id');

                $departures = array_map(function (Trip $trip) use ($input, $patternsById): DepartureDTO {
                    $pattern = $patternsById[$trip->patternId];

                    return DepartureDTO::create(
                        trip: $trip,
                        pattern: $pattern,
                        route: RouteDTO::create($pattern->route),
                        currentTime: $input->time,
                    );
                }, $trips);

                return StopWithDeparturesDTO::create(stop: $stop, departures: $departures);
            },
        );
    }

    /**
     * @param list<Trip> $trips
     *
     * @return array<int, int>
     */
    private function extractPatternIds(array $trips): array
    {
        return array_map(fn(Trip $trip) => $trip->patternId, $trips)
                |> array_unique(...)
                |> array_values(...);
    }
}
