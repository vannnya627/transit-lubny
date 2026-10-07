<?php

declare(strict_types=1);

namespace App\Actions\Stop;

use App\Caching\Interfaces\CacheInterface;
use App\Data\Entity\Trip\Trip;
use App\DTOs\DepartureDTO;
use App\DTOs\Input\GetStopDeparturesInput;
use App\DTOs\RouteDTO;
use App\DTOs\StopWithDeparturesDTO;
use App\Tasks\Route\FindPatternsWithRoutesByIdsTask;
use App\Tasks\Stop\GetStopByIdTask;
use App\Tasks\Trip\FindTripsWithStopTimesForStopTask;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class GetStopDeparturesAction
{
    public function __construct(
        private FindTripsWithStopTimesForStopTask $findTripsWithStopTimesForStopTask,
        private FindPatternsWithRoutesByIdsTask $findPatternsWithRoutesByIds,
        private GetStopByIdTask $getStopByIdTask,
        private CacheInterface $cache,
        #[Autowire(param: 'app.cache_ttl')]
        private int $ttlMinutes,
    ) {}

    public function run(GetStopDeparturesInput $input): StopWithDeparturesDTO
    {

        return $this->cache->get(
            key: "stop.$input->stopId.with.departures",
            ttlMinutes: $this->ttlMinutes,
            callback: function () use ($input) {

                // todo - тут знаходжу зупинку щоб взяти тільки з неї імя, можливо змінити
                $stop = $this->getStopByIdTask->run($input->stopId);
                $trips = $this->findTripsWithStopTimesForStopTask->run($input);

                $patternIds = $this->extractPatternIds($trips);
                $patterns = $this->findPatternsWithRoutesByIds->run($patternIds);

                $patternsById = array_column($patterns, null, 'id');
                $secondsFromMidnight = $input->getSecondsFromMidnight();

                $departures = array_map(function (Trip $trip) use ($secondsFromMidnight, $patternsById): DepartureDTO {
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

                return new StopWithDeparturesDTO(
                    stopId: $stop->id,
                    stopName: $stop->name,
                    cityCode: $stop->cityCode,
                    departures: $departures,
                );
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
