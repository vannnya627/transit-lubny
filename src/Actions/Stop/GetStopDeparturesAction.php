<?php

declare(strict_types=1);

namespace App\Actions\Stop;

use App\DTOs\DepartureDTO;
use App\DTOs\Input\GetStopDeparturesInput;
use App\DTOs\RouteDTO;
use App\Entity\Trip\Trip;
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

        $patterns = $this->findPatternsWithRoutesByIds->run($this->getPatternIds($trips));

        $patternsById = [];
        foreach ($patterns as $pattern) {
            $patternsById[$pattern->id] = $pattern;
        }

        $secondsFromMidnight =
            ((int) $input->time->format('H') * 3600) +
            ((int) $input->time->format('i') * 60) +
            ((int) $input->time->format('s'));

        return array_map(function (Trip $trip) use ($secondsFromMidnight, $patternsById): DepartureDTO {
            $pattern = $patternsById[$trip->patternId];
            $stopTime = $trip->getFirstStopTime();

            $hour = floor($stopTime->arrivalTime / 3600);
            $minute = floor(($stopTime->arrivalTime % 3600) / 60);
            $arrivalTime =  sprintf('%02d:%02d', $hour, $minute);

            $diffInSeconds = $stopTime->arrivalTime - $secondsFromMidnight;
            $expectedInMinutes = (int) floor($diffInSeconds / 60);

            return new DepartureDTO(
                tripId: $trip->id,
                route: new RouteDTO(id: $pattern->route->id, shortName: $pattern->route->shortName, longName: $pattern->route->longName),
                headsign: $pattern->name,
                arrivalTime: $arrivalTime,
                expectedInMinutes: $expectedInMinutes,
            );
        }, $trips);
    }

    /**
     * @param list<Trip> $trips
     *
     * @return array<int, int>
     */
    private function getPatternIds(array $trips): array
    {
        $patternIds = [];
        foreach ($trips as $trip) {
            $patternIds[] = $trip->patternId;
        }

        return array_unique($patternIds);
    }
}
