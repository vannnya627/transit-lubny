<?php

declare(strict_types=1);

namespace App\Tests\Unit\Actions\Stop;

use App\Actions\Stop\GetStopDeparturesAction;
use App\Caching\StopCache;
use App\Data\Entity\Route\Pattern;
use App\Data\Entity\Route\PatternStop;
use App\Data\Entity\Route\Route;
use App\Data\Entity\Stop;
use App\Data\Entity\Trip\StopTime;
use App\Data\Entity\Trip\Trip;
use App\Data\ValueObjects\Coordinates;
use App\Data\ValueObjects\ServiceDays;
use App\DTOs\DepartureDTO;
use App\DTOs\Input\GetStopDeparturesInput;
use App\DTOs\RouteDTO;
use App\DTOs\StopWithDeparturesDTO;
use App\Enum\Direction;
use App\Tasks\Route\FindPatternsWithRoutesByIdsTask;
use App\Tasks\Stop\GetStopByIdTask;
use App\Tasks\Trip\FindTripsWithStopTimesForStopTask;
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
    private StopCache|MockObject $cache;

    private GetStopDeparturesAction $action;

    protected function setUp(): void
    {
        $this->findTripsWithStopTimesForStopTask = $this->createMock(FindTripsWithStopTimesForStopTask::class);
        $this->findPatternsWithRoutesByIds = $this->createMock(FindPatternsWithRoutesByIdsTask::class);
        $this->getStopByIdTask = $this->createMock(GetStopByIdTask::class);
        $this->cache = $this->createMock(StopCache::class);
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
        );

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

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturnCallback(function (string $key, callable $callback) use ($input) {
                $this->assertEquals("stop.$input->stopId.with.departures", $key);

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

        $departuresDTOs = new DepartureDTO(
            tripId: $trip->id,
            route: RouteDTO::create($pattern->route),
            headsign: $pattern->name,
            arrivalTime: $stopTime->getFormattedArrivalTime(),
            expectedInMinutes: $stopTime->getMinutesUntil($input->getSecondsFromMidnight()),
        );

        $expected = new StopWithDeparturesDTO(
            stopId: $stop->id,
            stopName: $stop->name,
            cityCode: $stop->cityCode,
            departures: [$departuresDTOs],
        );
        $result = $this->action->run($input);
        $this->assertEquals($expected, $result);

    }
}
