<?php

declare(strict_types=1);

namespace App\Application\Actions\Trip;

use App\Application\Caching\TripCache;
use App\Application\DTOs\Inputs\GetTripInput;
use App\Application\DTOs\RouteDTO;
use App\Application\DTOs\StopDTO;
use App\Application\DTOs\StopForTripDTO;
use App\Application\DTOs\TripDetailsDTO;
use App\Application\Tasks\Route\GetPatternWithRouteByPatternIdTask;
use App\Application\Tasks\Stop\GetStopsByIdsTask;
use App\Application\Tasks\Trip\GetTripByIdTask;
use App\Domain\Entity\Stop;
use App\Domain\Entity\Trip\StopTime;

final readonly class GetTripAction
{
    public function __construct(
        private GetTripByIdTask $getTripByIdTask,
        private GetPatternWithRouteByPatternIdTask $getPatternWithRouteByPatternIdTask,
        private GetStopsByIdsTask $getStopsByIdsTask,
        private TripCache $cache,
    ) {}

    public function run(GetTripInput $input): TripDetailsDTO
    {
        /** @var TripDetailsDTO $tripDetailsDTO */
        $tripDetailsDTO = $this->cache->get(
            key: "trip.$input->tripId",
            callback: function () use ($input) {
                $trip = $this->getTripByIdTask->run($input->tripId);
                $pattern = $this->getPatternWithRouteByPatternIdTask->run($trip->patternId);

                $stopTimes = $trip->stopTimes->getValues();
                $stopIds = array_column($stopTimes, 'stopId');
                $stops = $this->getStopsByIdsTask->run($stopIds);

                $sortedStopsById = array_column($stops, null, 'id');

                $stopForTripDTOs = array_map(function (StopTime $stopTime) use ($sortedStopsById) {

                    /** @var Stop $stop */
                    $stop = $sortedStopsById[$stopTime->stopId];

                    return StopForTripDTO::create(
                        stop: StopDTO::create($stop),
                        stopTime: $stopTime,
                    );
                }, $stopTimes);

                return TripDetailsDTO::create(
                    trip: $trip,
                    pattern: $pattern,
                    route: RouteDTO::create($pattern->route),
                    stops: $stopForTripDTOs,
                );
            },
        );

        $stops = array_map(
            fn(StopForTripDTO $stop) => $stop->withIsPassed($input->getSecondsFromMidnight()),
            $tripDetailsDTO->stops,
        );

        return $tripDetailsDTO->withStops($stops);

    }
}
