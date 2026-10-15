<?php

declare(strict_types=1);

namespace App\Actions\Route;

use App\Caching\RouteCache;
use App\Data\Entity\Route\Pattern;
use App\Data\Entity\Route\PatternStop;
use App\Data\Entity\Trip\Trip;
use App\DTOs\PatternWithStopsAndTripsDTO;
use App\DTOs\StopForScheduleDTO;
use App\DTOs\TripDTO;
use App\Tasks\Route\GetPatternByIdTask;
use App\Tasks\Stop\GetStopsByIdsTask;
use App\Tasks\Trip\FindTripsWithStopTimesByPatternIdTask;

final readonly class GetPatternWithTripsAndStopsAction
{
    public function __construct(
        private GetPatternByIdTask $getPatternByIdTask,
        private GetStopsByIdsTask $getStopsByIdsTask,
        private FindTripsWithStopTimesByPatternIdTask $findTripsWithStopTimesByPatternIdTask,
        private RouteCache $cache,
    ) {}

    public function run(int $patternId): PatternWithStopsAndTripsDTO
    {
        return $this->cache->get(
            key: "pattern.$patternId",
            callback: function () use ($patternId): PatternWithStopsAndTripsDTO {
                $pattern = $this->getPatternByIdTask->run($patternId);

                $patternStops = $this->getPatternStops($pattern);
                $stopIds = array_column($patternStops, 'stopId');
                $stops = $this->getStopsByIdsTask->run($stopIds);

                $trips = $this->findTripsWithStopTimesByPatternIdTask->run($patternId);

                $stopsForScheduleDTOs = array_map(StopForScheduleDTO::create(...), $stops, $patternStops);

                $tripsDTOs = $this->getTripsDTOs($trips);

                return PatternWithStopsAndTripsDTO::create(
                    pattern: $pattern,
                    stops: $stopsForScheduleDTOs,
                    trips: $tripsDTOs,
                );
            },
        );
    }

    /**
     * @return list<PatternStop>
     */
    private function getPatternStops(Pattern $pattern): array
    {
        $patternStops = array_values($pattern->patternStops->toArray());

        usort($patternStops, static fn(PatternStop $a, PatternStop $b): int => $a->stopSequence <=> $b->stopSequence);

        return $patternStops;
    }

    /**
     * @param list<Trip> $trips
     *
     * @return list<TripDTO>
     */
    private function getTripsDTOs(array $trips): array
    {
        return array_map(function (Trip $trip): TripDTO {

            $stopTimes = array_values($trip->stopTimes->toArray());

            $stopTimesStr = [];
            foreach ($stopTimes as $stopTime) {
                $stopTimesStr[$stopTime->stopSequence - 1] = $stopTime->getFormattedArrivalTime();
            }

            return TripDTO::create(trip: $trip, stopTimeStr: $stopTimesStr);
        }, $trips);
    }
}
