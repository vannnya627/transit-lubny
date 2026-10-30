<?php

declare(strict_types=1);

namespace App\Application\Actions\Route;

use App\Application\Caching\RouteCache;
use App\Application\DTOs\PatternWithStopsAndTripsDTO;
use App\Application\DTOs\StopForScheduleDTO;
use App\Application\DTOs\TripDTO;
use App\Application\Tasks\Route\GetPatternByIdTask;
use App\Application\Tasks\Stop\GetStopsByIdsTask;
use App\Application\Tasks\Trip\FindTripsWithStopTimesByPatternIdTask;
use App\Domain\Entity\Trip\Trip;

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

                $patternStops = $pattern->patternStops->getValues();

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
     * @param list<Trip> $trips
     *
     * @return list<TripDTO>
     */
    private function getTripsDTOs(array $trips): array
    {
        return array_map(function (Trip $trip): TripDTO {
            $stopTimesStr = [];
            foreach ($trip->stopTimes as $stopTime) {
                $stopTimesStr[$stopTime->stopSequence - 1] = $stopTime->getFormattedArrivalTime();
            }

            return TripDTO::create(trip: $trip, stopTimeStr: $stopTimesStr);
        }, $trips);
    }
}
