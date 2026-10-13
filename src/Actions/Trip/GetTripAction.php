<?php

declare(strict_types=1);

namespace App\Actions\Trip;

use App\Caching\TripCache;
use App\Data\Entity\Stop;
use App\Data\Entity\Trip\StopTime;
use App\DTOs\RouteDTO;
use App\DTOs\StopDTO;
use App\DTOs\StopForTripDTO;
use App\DTOs\TripDetailsDTO;
use App\Tasks\Route\GetPatternWithRouteByPatternIdTask;
use App\Tasks\Stop\FindStopsByIdsTask;
use App\Tasks\Trip\GetTripByIdTask;
use DateTimeImmutable;
use DateTimeZone;

final readonly class GetTripAction
{
    public function __construct(
        private GetTripByIdTask $getTripByIdTask,
        private GetPatternWithRouteByPatternIdTask $getPatternWithRouteByPatternIdTask,
        private FindStopsByIdsTask $findStopsByIdsTask,
        private TripCache $cache,
    ) {}

    public function run(int $tripId, ?DateTimeImmutable $now = null): TripDetailsDTO
    {
        return $this->cache->get(
            key: "trip.$tripId",
            callback: function () use ($tripId, $now) {
                $trip = $this->getTripByIdTask->run($tripId);
                $pattern = $this->getPatternWithRouteByPatternIdTask->run($trip->patternId);

                $stopTimes = $trip->stopTimes->toArray();
                $stops = $this->findStopsByIdsTask->run(array_column($stopTimes, 'stopId'));

                $sortedStopsById = array_column($stops, null, 'id');

                $now ??= new DateTimeImmutable('now', new DateTimeZone('Europe/Kyiv'));
                $secondsFromMidnight = $this->getSecondsFromMidnight($now);

                $stopForTripDTOs = array_map(function (StopTime $stopTime) use ($secondsFromMidnight, $sortedStopsById) {

                    /** @var Stop $stop */
                    $stop = $sortedStopsById[$stopTime->stopId];

                    return StopForTripDTO::create(
                        stop: StopDTO::create($stop),
                        stopTime: $stopTime,
                        secondsFromMidnight: $secondsFromMidnight,
                    );
                }, $stopTimes);


                return TripDetailsDTO::create(
                    trip: $trip,
                    pattern: $pattern,
                    route: RouteDTO::create($pattern->route),
                    stops: array_values($stopForTripDTOs),
                );
            },
        );
    }

    private function getSecondsFromMidnight(DateTimeImmutable $now): int
    {
        $secondsFromMidnight =
            ((int) $now->format('H') * 3600) +
            ((int) $now->format('i') * 60) +
            ((int) $now->format('s'));

        return (int) $secondsFromMidnight;
    }
}
