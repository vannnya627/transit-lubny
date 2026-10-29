<?php

declare(strict_types=1);

namespace App\Tests\Unit\Actions\Route;

use App\Actions\Route\GetPatternWithTripsAndStopsAction;
use App\Caching\RouteCache;
use App\Data\Entity\Route\Route;
use App\Data\Entity\Stop;
use App\Data\Entity\Trip\Trip;
use App\Data\ValueObjects\Coordinates;
use App\Data\ValueObjects\ServiceDays;
use App\DTOs\PatternWithStopsAndTripsDTO;
use App\DTOs\StopForScheduleDTO;
use App\DTOs\TripDTO;
use App\Enum\Direction;
use App\Tasks\Route\GetPatternByIdTask;
use App\Tasks\Stop\GetStopsByIdsTask;
use App\Tasks\Trip\FindTripsWithStopTimesByPatternIdTask;
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
    private RouteCache|MockObject $cache;
    private GetPatternWithTripsAndStopsAction $action;

    protected function setUp(): void
    {
        $this->getPatternByIdTask = $this->createMock(GetPatternByIdTask::class);
        $this->findStopsByIdsTask = $this->createMock(GetStopsByIdsTask::class);
        $this->findTripsWithStopTimesByPatternIdTask = $this->createMock(FindTripsWithStopTimesByPatternIdTask::class);
        $this->cache = $this->createMock(RouteCache::class);
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
            arrivalTime: 57600,
            isEstimate: true,
        );

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturnCallback(function (string $key, callable $callback) use ($pattern) {
                $this->assertEquals("pattern.$pattern->id", $key);

                return $callback();
            });

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
