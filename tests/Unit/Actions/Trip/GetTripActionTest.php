<?php

declare(strict_types=1);

namespace App\Tests\Unit\Actions\Trip;

use App\Actions\Trip\GetTripAction;
use App\Data\Entity\Route\Pattern;
use App\Data\Entity\Route\PatternStop;
use App\Data\Entity\Route\Route;
use App\Data\Entity\Stop;
use App\Data\Entity\Trip\StopTime;
use App\Data\Entity\Trip\Trip;
use App\Data\ValueObjects\Coordinates;
use App\Data\ValueObjects\ServiceDays;
use App\DTOs\RouteDTO;
use App\DTOs\StopDTO;
use App\DTOs\StopForTripDTO;
use App\DTOs\TripDetailsDTO;
use App\Enum\Direction;
use App\Tasks\Route\GetPatternWithRouteByPatternIdTask;
use App\Tasks\Stop\FindStopsByIdsTask;
use App\Tasks\Trip\GetTripByIdTask;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class GetTripActionTest extends AbstractTestCase
{
    private GetTripByIdTask|MockObject $getTripByIdTask;
    private GetPatternWithRouteByPatternIdTask|MockObject $getPatternWithRouteByPatternIdTask;
    private FindStopsByIdsTask|MockObject $findStopsByIdsTask;
    private GetTripAction $action;

    protected function setUp(): void
    {
        $this->getTripByIdTask = $this->createMock(GetTripByIdTask::class);
        $this->getPatternWithRouteByPatternIdTask = $this->createMock(GetPatternWithRouteByPatternIdTask::class);
        $this->findStopsByIdsTask = $this->createMock(FindStopsByIdsTask::class);
        $this->action = new GetTripAction(
            $this->getTripByIdTask,
            $this->getPatternWithRouteByPatternIdTask,
            $this->findStopsByIdsTask,
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testRun(): void
    {
        $tripId = 1;

        $routeId = 1;
        $route = Route::create(shortName: '1', longName: 'test');
        $this->setEntityId($route, $routeId);

        $patternId = 1;
        $pattern = Pattern::create(
            route: $route,
            direction: Direction::FORWARD,
            name: 'test',
        );
        $this->setEntityId($pattern, $patternId);

        $stopId = 1;
        $stop = Stop::create(
            name: 'test',
            coordinates: Coordinates::create(50.0, 32.0),
            cityCode: 1,
        );
        $this->setEntityId($stop, $stopId);

        $patternStop = PatternStop::create(
            pattern: $pattern,
            stopSequence: 1,
            stopId: $stop->id,
        );
        $pattern->addPatternStop($patternStop);

        $trip = Trip::create(
            patternId: $pattern->id,
            serviceDays: ServiceDays::everyday(),
        );
        $this->setEntityId($trip, $tripId);

        $stopTime = StopTime::create(
            trip: $trip,
            stopSequence: 1,
            stopId: $stop->id,
            arrivalTime: 61200,
            isEstimate: true,
        );
        $trip->addStopTime($stopTime);

        $this->getTripByIdTask->expects($this->once())
            ->method('run')
            ->with($tripId)
            ->willReturn($trip);

        $this->getPatternWithRouteByPatternIdTask->expects($this->once())
            ->method('run')
            ->with($trip->patternId)
            ->willReturn($pattern);

        $stopTimes = $trip->stopTimes->toArray();
        $this->findStopsByIdsTask->expects($this->once())
            ->method('run')
            ->with(array_column($stopTimes, 'stopId'))
            ->willReturn([$stop]);

        $stopForTripDTOs = new StopForTripDTO(
            sequence: $stopTime->stopSequence,
            stop: new StopDTO(
                id: $stop->id,
                name: $stop->name,
                coordinates: $stop->coordinates,
                cityCode: $stop->cityCode,
            ),
            arrivalTime: $stopTime->getFormattedArrivalTime(),
            isPassed: $stopTime->isPassed(57600),
        );
        $expected = new TripDetailsDTO(
            tripId: $trip->id,
            route: new RouteDTO(id: $pattern->route->id, shortName: $pattern->route->shortName, longName: $pattern->route->longName),
            headsign: $pattern->name,
            stops: [$stopForTripDTOs],
        );

        $result = $this->action->run($tripId);
        $this->assertEquals($expected, $result);
    }
}
