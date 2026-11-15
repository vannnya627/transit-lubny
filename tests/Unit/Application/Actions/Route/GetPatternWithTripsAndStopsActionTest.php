<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Actions\Route;

use App\Application\Actions\Route\GetPatternWithTripsAndStopsAction;
use App\Application\Caching\RouteCacheInterface;
use App\Application\DTOs\PatternWithStopsAndTripsDTO;
use App\Application\DTOs\StopForScheduleDTO;
use App\Application\DTOs\TripDTO;
use App\Application\Tasks\Pattern\GetPatternByIdTask;
use App\Application\Tasks\Stop\GetStopsByIdsTask;
use App\Application\Tasks\Trip\FindTripsWithStopTimesByPatternIdTask;
use App\Domain\Entity\Route\Route;
use App\Domain\Entity\Stop;
use App\Domain\Entity\Trip\Trip;
use App\Domain\Enums\Direction;
use App\Domain\ValueObjects\Coordinates;
use App\Domain\ValueObjects\ServiceDays;
use App\Domain\ValueObjects\TimeOfDay;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class GetPatternWithTripsAndStopsActionTest extends AbstractTestCase
{
    private GetPatternByIdTask|MockObject $getPatternByIdTask;
    private GetStopsByIdsTask|MockObject $findStopsByIdsTask;
    private FindTripsWithStopTimesByPatternIdTask|MockObject $findTripsWithStopTimesByPatternIdTask;
    private RouteCacheInterface|MockObject $cache;
    private GetPatternWithTripsAndStopsAction $action;

    protected function setUp(): void
    {
        $this->getPatternByIdTask = $this->createMock(GetPatternByIdTask::class);
        $this->findStopsByIdsTask = $this->createMock(GetStopsByIdsTask::class);
        $this->findTripsWithStopTimesByPatternIdTask = $this->createMock(FindTripsWithStopTimesByPatternIdTask::class);
        $this->cache = $this->createMock(RouteCacheInterface::class);
        $this->action = new GetPatternWithTripsAndStopsAction(
            $this->getPatternByIdTask,
            $this->findStopsByIdsTask,
            $this->findTripsWithStopTimesByPatternIdTask,
            $this->cache,
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
        $pattern = $route->createPattern(Direction::FORWARD, 'test');
        $this->setEntityId($pattern, $patternId);

        $stopId = 1;
        $stop = Stop::create(
            name: 'test',
            coordinates: Coordinates::create(50.0, 32.0),
            cityCode: 1,
        );
        $this->setEntityId($stop, $stopId);

        $patternStop = $pattern->createPatternStop(1, $stop->id);

        $tripId = 1;
        $trip = Trip::create(
            patternId: $pattern->id,
            serviceDays: ServiceDays::everyday(),
        );
        $this->setEntityId($trip, $tripId);

        $stopTime = $trip->createStopTime(
            stopSequence: 1,
            stopId: $stop->id,
            arrivalTime: TimeOfDay::fromSeconds(57600),
            isEstimate: true,
        );

        $this->cache->expects($this->once())
            ->method('getPatternWithStopsAndTrips')
            ->with($pattern->id)
            ->willReturnCallback(fn(int $patternId, callable $callback) => $callback());

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

        $stopsForScheduleDTOs = StopForScheduleDTO::create(stop: $stop, patternStop: $patternStop);

        $tripsDTOs =  TripDTO::create(trip: $trip, stopTimeStr: ['16:00']);

        $expected =   PatternWithStopsAndTripsDTO::create(
            pattern: $pattern,
            stops: [$stopsForScheduleDTOs],
            trips: [$tripsDTOs],
        );

        $result = $this->action->run($patternId);
        $this->assertEquals($expected, $result);
    }
}
