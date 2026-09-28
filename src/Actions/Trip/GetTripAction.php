<?php

declare(strict_types=1);

namespace App\Actions\Trip;

use App\DTOs\RouteDTO;
use App\DTOs\StopDTO;
use App\DTOs\StopForTripDTO;
use App\DTOs\TripDetailsDTO;
use App\Entity\Stop;
use App\Entity\Trip\StopTime;
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
    ) {}

    public function run(int $tripId): TripDetailsDTO
    {
        $trip = $this->getTripByIdTask->run($tripId);
        $pattern = $this->getPatternWithRouteByPatternIdTask->run($trip->patternId);

        $stops = $this->findStopsByIdsTask->run(array_column($trip->stopTimes->toArray(), 'stopId'));

        $sortedStopsById = array_column($stops, null, 'id');

        $secondsFromMidnight = $this->getSecondsFromMidnight(new DateTimeImmutable('now', new DateTimeZone('Europe/Kyiv')));

        $stopForTripDTOs = array_map(function (StopTime $stopTime) use ($secondsFromMidnight, $sortedStopsById) {

            /** @var Stop $stop */
            $stop = $sortedStopsById[$stopTime->stopId];

            $arrivalTime = gmdate('H:i', $stopTime->arrivalTime);

            return new StopForTripDTO(
                sequence: $stopTime->stopSequence,
                stop: new StopDTO(
                    id: $stop->id,
                    name: $stop->name,
                    coordinates: $stop->coordinates,
                    cityCode: $stop->cityCode,
                ),
                arrivalTime: $arrivalTime,
                isPassed: $stopTime->arrivalTime < $secondsFromMidnight,
            );
        }, $trip->stopTimes->toArray());


        return new TripDetailsDTO(
            tripId: $tripId,
            route: new RouteDTO(id: $pattern->route->id, shortName: $pattern->route->shortName, longName: $pattern->route->longName),
            headsign: $pattern->name,
            stops: array_values($stopForTripDTOs),
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
