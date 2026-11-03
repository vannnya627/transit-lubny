<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Actions\Stop;

use App\Application\Actions\Stop\GetStopDeparturesAction;
use App\Application\Caching\DepartureCache;
use App\Application\DTOs\DepartureDTO;
use App\Application\DTOs\Inputs\GetStopDeparturesInput;
use App\Application\DTOs\RouteDTO;
use App\Application\DTOs\StopWithDeparturesDTO;
use App\Application\Tasks\Pattern\FindPatternsWithRoutesByIdsTask;
use App\Application\Tasks\Stop\GetStopByIdTask;
use App\Application\Tasks\Trip\FindTripsWithStopTimesForStopTask;
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
class GetStopDeparturesActionTest extends AbstractTestCase
{
    private FindTripsWithStopTimesForStopTask|MockObject $findTripsWithStopTimesForStopTask;
    private FindPatternsWithRoutesByIdsTask|MockObject $findPatternsWithRoutesByIds;
    private GetStopByIdTask|MockObject $getStopByIdTask;
    private DepartureCache|MockObject $cache;

    private GetStopDeparturesAction $action;

    protected function setUp(): void
    {
        $this->findTripsWithStopTimesForStopTask = $this->createMock(FindTripsWithStopTimesForStopTask::class);
        $this->findPatternsWithRoutesByIds = $this->createMock(FindPatternsWithRoutesByIdsTask::class);
        $this->getStopByIdTask = $this->createMock(GetStopByIdTask::class);
        $this->cache = $this->createMock(DepartureCache::class);
        $this->action = new GetStopDeparturesAction(
            $this->findTripsWithStopTimesForStopTask,
            $this->findPatternsWithRoutesByIds,
            $this->getStopByIdTask,
            $this->cache,
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testRun(): void
    {
        $input = new GetStopDeparturesInput(
            stopId: 1,
            time: TimeOfDay::now(),
        );

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
            ->method('get')
            ->willReturnCallback(function (string $key, callable $callback) use ($input) {
                $this->assertEquals("stop.$input->stopId.time.{$input->time->value}", $key);

                return $callback();
            });

        $this->getStopByIdTask->expects($this->once())
            ->method('run')
            ->with($stopId)
            ->willReturn($stop);

        $this->findTripsWithStopTimesForStopTask->expects($this->once())
            ->method('run')
            ->with($input)
            ->willReturn([$trip]);

        $this->findPatternsWithRoutesByIds->expects($this->once())
            ->method('run')
            ->with([$patternId])
            ->willReturn([$pattern]);

        $departuresDTOs = DepartureDTO::create(
            trip: $trip,
            pattern: $pattern,
            route: RouteDTO::create($route),
            currentTime: $input->time,
        );

        $expected = StopWithDeparturesDTO::create(stop: $stop, departures: [$departuresDTOs]);
        $result = $this->action->run($input);
        $this->assertEquals($expected, $result);

    }
}
