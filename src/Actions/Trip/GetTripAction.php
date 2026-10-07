<?php

declare(strict_types=1);

namespace App\Actions\Trip;

use App\Caching\Interfaces\CacheInterface;
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
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class GetTripAction
{
    public function __construct(
        private GetTripByIdTask $getTripByIdTask,
        private GetPatternWithRouteByPatternIdTask $getPatternWithRouteByPatternIdTask,
        private FindStopsByIdsTask $findStopsByIdsTask,
        private CacheInterface $cache,
        #[Autowire(param: 'app.cache_ttl')]
        private int $ttlMinutes,
    ) {}

    public function run(int $tripId, ?DateTimeImmutable $now = null): TripDetailsDTO
    {
        return $this->cache->get(
            key: "trip.$tripId",
            ttlMinutes: $this->ttlMinutes,
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

                    return new StopForTripDTO(
                        sequence: $stopTime->stopSequence,
                        stop: new StopDTO(
                            id: $stop->id,
                            name: $stop->name,
                            coordinates: $stop->coordinates,
                            cityCode: $stop->cityCode,
                        ),
                        arrivalTime: $stopTime->getFormattedArrivalTime(),
                        isPassed: $stopTime->isPassed($secondsFromMidnight),
                    );
                }, $stopTimes);


                return new TripDetailsDTO(
                    tripId: $tripId,
                    route: new RouteDTO(id: $pattern->route->id, shortName: $pattern->route->shortName, longName: $pattern->route->longName),
                    headsign: $pattern->name,
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
