<?php

declare(strict_types=1);

namespace App\Actions\Route;

use App\DTOs\PatternWithStopsAndTripsDTO;
use App\DTOs\StopDTO;
use App\DTOs\TripDTO;
use App\Entity\Route\PatternStop;
use App\Entity\Stop;
use App\Entity\Trip\StopTime;
use App\Entity\Trip\Trip;
use App\Tasks\Route\GetPatternByIdTask;
use App\Tasks\Stop\FindStopsByIdsTask;
use App\Tasks\Trip\FindTripsWithStopTimesByPatternIdTask;

final readonly class GetPatternWithTripsAndStopsAction
{
    public function __construct(
        private GetPatternByIdTask $getPatternByIdTask,
        private FindStopsByIdsTask $findStopsByIdsTask,
        private FindTripsWithStopTimesByPatternIdTask $findTripsWithStopTimesByPatternIdTask,
    ) {}

    public function run(int $patternId): PatternWithStopsAndTripsDTO
    {
        $pattern = $this->getPatternByIdTask->run($patternId);
        $patternStops = array_values($pattern->patternStops->toArray());

        $stopIds = $this->getStopsIds($patternStops);

        $stops = $this->findStopsByIdsTask->run($stopIds);
        $trips = $this->findTripsWithStopTimesByPatternIdTask->run($patternId);

        $stopsDTOs = array_map(
            fn(Stop $stop, $patternStop): StopDTO => new StopDTO(
                sequence: $patternStop->stopSequence,
                stopId: $stop->id,
                name: $stop->name,
            ),
            $stops,
            $patternStops,
        );

        $tripsDTOs = array_map(function (Trip $trip): TripDTO {

            $stopTimes = array_values($trip->stopTimes->toArray());
            $stopTimesStr = $this->getStopTimeStr($stopTimes);

            ksort($stopTimesStr);

            return new TripDTO(
                tripId: $trip->id,
                serviceDays: $trip->serviceDays,
                stopTimes: $stopTimesStr,
            );
        }, $trips);

        return new PatternWithStopsAndTripsDTO(
            id: $pattern->id,
            direction: $pattern->direction,
            name: $pattern->name,
            stops: $stopsDTOs,
            trips: $tripsDTOs,
        );

    }

    /**
     * @param list<PatternStop> $patternStops
     *
     * @return array<int, int>
     */
    private function getStopsIds(array $patternStops): array
    {
        $stopIds = [];
        foreach ($patternStops as $patternStop) {
            $stopIds[] = $patternStop->stopId;
        }

        return $stopIds;
    }

    /**
     * @param list<StopTime> $stopTimes
     *
     * @return array<int, string>
     */
    private function getStopTimeStr(array $stopTimes): array
    {
        $stopTimesStr = [];
        foreach ($stopTimes as $stopTime) {
            $hour = floor($stopTime->arrivalTime / 3600);
            $minute = floor(($stopTime->arrivalTime % 3600) / 60);

            $stopTimesStr[$stopTime->stopSequence - 1] = sprintf('%02d:%02d', $hour, $minute);
        }

        return $stopTimesStr;
    }
}
