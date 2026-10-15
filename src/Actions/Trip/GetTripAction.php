<?php

declare(strict_types=1);

namespace App\Actions\Trip;

use App\Caching\TripCache;
use App\Data\Entity\Stop;
use App\Data\Entity\Trip\StopTime;
use App\DTOs\Input\GetTripInput;
use App\DTOs\RouteDTO;
use App\DTOs\StopDTO;
use App\DTOs\StopForTripDTO;
use App\DTOs\TripDetailsDTO;
use App\Tasks\Route\GetPatternWithRouteByPatternIdTask;
use App\Tasks\Stop\GetStopsByIdsTask;
use App\Tasks\Trip\GetTripByIdTask;

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

        foreach ($tripDetailsDTO->stops as $stop) {
            $stop->checkIsPassed($input->getSecondsFromMidnight());
        }

        return $tripDetailsDTO;
    }
}
