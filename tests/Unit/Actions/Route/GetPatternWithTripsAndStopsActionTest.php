<?php

declare(strict_types=1);

namespace App\Tests\Unit\Actions\Route;

use App\Actions\Route\GetPatternWithTripsAndStopsAction;
use App\Data\Entity\Route\Pattern;
use App\Data\Entity\Route\PatternStop;
use App\Data\Entity\Route\Route;
use App\Data\Entity\Stop;
use App\Data\Entity\Trip\StopTime;
use App\Data\Entity\Trip\Trip;
use App\Data\ValueObjects\Coordinates;
use App\Data\ValueObjects\ServiceDays;
use App\DTOs\PatternWithStopsAndTripsDTO;
use App\DTOs\StopForScheduleDTO;
use App\DTOs\TripDTO;
use App\Enum\Direction;
use App\Tasks\Route\GetPatternByIdTask;
use App\Tasks\Stop\FindStopsByIdsTask;
use App\Tasks\Trip\FindTripsWithStopTimesByPatternIdTask;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class GetPatternWithTripsAndStopsActionTest extends AbstractTestCase
{
    private GetPatternByIdTask|MockObject $getPatternByIdTask;
    private FindStopsByIdsTask|MockObject $findStopsByIdsTask;
    private FindTripsWithStopTimesByPatternIdTask|MockObject $findTripsWithStopTimesByPatternIdTask;
    private GetPatternWithTripsAndStopsAction $action;

    protected function setUp(): void
    {
        $this->getPatternByIdTask = $this->createMock(GetPatternByIdTask::class);
        $this->findStopsByIdsTask = $this->createMock(FindStopsByIdsTask::class);
        $this->findTripsWithStopTimesByPatternIdTask = $this->createMock(FindTripsWithStopTimesByPatternIdTask::class);
        $this->action = new GetPatternWithTripsAndStopsAction(
            $this->getPatternByIdTask,
            $this->findStopsByIdsTask,
            $this->findTripsWithStopTimesByPatternIdTask,
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testRun(): void
    {

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

        $tripId = 1;
        $trip = Trip::create(
            patternId: $pattern->id,
            serviceDays: ServiceDays::everyday(),
        );
        $this->setEntityId($trip, $tripId);

        $stopTime = StopTime::create(
            trip: $trip,
            stopSequence: 1,
            stopId: $stop->id,
            arrivalTime: 57600,
            isEstimate: true,
        );
        $trip->addStopTime($stopTime);

        $this->getPatternByIdTask->expects($this->once())
            ->method('run')
            ->with($patternId)
            ->willReturn($pattern);

        $stopIds = [$stop->id];
        $this->findStopsByIdsTask->expects($this->once())
            ->method('run')
            ->with($stopIds)
            ->willReturn([$stop]);

        $this->findTripsWithStopTimesByPatternIdTask->expects($this->once())
            ->method('run')
            ->with($pattern->id)
            ->willReturn([$trip]);

        $stopsForScheduleDTOs = new StopForScheduleDTO(
            sequence: $patternStop->stopSequence,
            stopId: $stop->id,
            name: $stop->name,
        );

        $tripsDTOs =  new TripDTO(
            tripId: $trip->id,
            serviceDays: $trip->serviceDays,
            stopTimes: ['16:00'],
        );

        $expected =   new PatternWithStopsAndTripsDTO(
            id: $pattern->id,
            direction: $pattern->direction,
            name: $pattern->name,
            stops: [$stopsForScheduleDTOs],
            trips: [$tripsDTOs],
        );

        $result = $this->action->run($patternId);
        $this->assertEquals($expected, $result);
    }
}
