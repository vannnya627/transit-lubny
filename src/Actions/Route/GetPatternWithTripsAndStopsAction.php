<?php

declare(strict_types=1);

namespace App\Actions\Route;

use App\Caching\RouteCache;
use App\Data\Entity\Stop;
use App\Data\Entity\Trip\StopTime;
use App\Data\Entity\Trip\Trip;
use App\DTOs\PatternWithStopsAndTripsDTO;
use App\DTOs\StopForScheduleDTO;
use App\DTOs\TripDTO;
use App\Tasks\Route\GetPatternByIdTask;
use App\Tasks\Stop\FindStopsByIdsTask;
use App\Tasks\Trip\FindTripsWithStopTimesByPatternIdTask;

final readonly class GetPatternWithTripsAndStopsAction
{
    public function __construct(
        private GetPatternByIdTask $getPatternByIdTask,
        private FindStopsByIdsTask $findStopsByIdsTask,
        private FindTripsWithStopTimesByPatternIdTask $findTripsWithStopTimesByPatternIdTask,
        private RouteCache $cache,
    ) {}

    public function run(int $patternId): PatternWithStopsAndTripsDTO
    {
        return $this->cache->get(
            key: "pattern.$patternId",
            callback: function () use ($patternId): PatternWithStopsAndTripsDTO {
                $pattern = $this->getPatternByIdTask->run($patternId);

                $patternStops = array_values($pattern->patternStops->toArray());
                usort($patternStops, static fn($a, $b): int => $a->stopSequence <=> $b->stopSequence);
                $stopIds = array_column($patternStops, 'stopId');

                $stops = $this->findStopsByIdsTask->run($stopIds);
                $trips = $this->findTripsWithStopTimesByPatternIdTask->run($patternId);

                $stopsForScheduleDTOs = array_map(
                    fn(Stop $stop, $patternStop): StopForScheduleDTO => new StopForScheduleDTO(
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

                    return new TripDTO(
                        tripId: $trip->id,
                        serviceDays: $trip->serviceDays,
                        stopTimes: $stopTimesStr,
                    );
                }, $trips);

                usort(
                    $tripsDTOs,
                    static fn(TripDTO $a, TripDTO $b): int => ($a->stopTimes[0] ?? '') <=> ($b->stopTimes[0] ?? ''),
                );

                return new PatternWithStopsAndTripsDTO(
                    id: $pattern->id,
                    direction: $pattern->direction,
                    name: $pattern->name,
                    stops: $stopsForScheduleDTOs,
                    trips: $tripsDTOs,
                );
            },
        );
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
            $stopTimesStr[$stopTime->stopSequence - 1] = $stopTime->getFormattedArrivalTime();
        }

        ksort($stopTimesStr);

        return $stopTimesStr;
    }
}
