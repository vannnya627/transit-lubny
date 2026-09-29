<?php

declare(strict_types=1);

namespace App\Actions\Stop;

use App\Data\Entity\Trip\Trip;
use App\DTOs\DepartureDTO;
use App\DTOs\Input\GetStopDeparturesInput;
use App\DTOs\RouteDTO;
use App\Tasks\Route\FindPatternsWithRoutesByIdsTask;
use App\Tasks\Trip\FindTripsWithStopTimesForStopTask;

final readonly class GetStopDeparturesAction
{
    public function __construct(
        private FindTripsWithStopTimesForStopTask $findTripsWithStopTimesForStopTask,
        private FindPatternsWithRoutesByIdsTask $findPatternsWithRoutesByIds,
    ) {}

    /**
     * @return list<DepartureDTO>
     */
    public function run(GetStopDeparturesInput $input): array
    {
        $trips = $this->findTripsWithStopTimesForStopTask->run($input);

        $patternIds = $this->extractPatternIds($trips);
        $patterns = $this->findPatternsWithRoutesByIds->run($patternIds);

        $patternsById = array_column($patterns, null, 'id');
        $secondsFromMidnight = $input->getSecondsFromMidnight();

        return array_map(function (Trip $trip) use ($secondsFromMidnight, $patternsById): DepartureDTO {
            $pattern = $patternsById[$trip->patternId];
            $stopTime = $trip->getFirstStopTime();

            return new DepartureDTO(
                tripId: $trip->id,
                route: new RouteDTO(id: $pattern->route->id, shortName: $pattern->route->shortName, longName: $pattern->route->longName),
                headsign: $pattern->name,
                arrivalTime: $stopTime->getFormattedArrivalTime(),
                expectedInMinutes: $stopTime->getMinutesUntil($secondsFromMidnight),
            );
        }, $trips);
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
