<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Actions\Trip;

use App\Application\Actions\Trip\GetTripAction;
use App\Application\Caching\TripCache;
use App\Application\DTOs\Inputs\GetTripInput;
use App\Application\DTOs\RouteDTO;
use App\Application\DTOs\StopDTO;
use App\Application\DTOs\StopForTripDTO;
use App\Application\DTOs\TripDetailsDTO;
use App\Application\Tasks\Pattern\GetPatternWithRouteByPatternIdTask;
use App\Application\Tasks\Stop\GetStopsByIdsTask;
use App\Application\Tasks\Trip\GetTripByIdTask;
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
class GetTripActionTest extends AbstractTestCase
{
    private GetTripByIdTask|MockObject $getTripByIdTask;
    private GetPatternWithRouteByPatternIdTask|MockObject $getPatternWithRouteByPatternIdTask;
    private GetStopsByIdsTask|MockObject $findStopsByIdsTask;
    private TripCache|MockObject $cache;
    private GetTripAction $action;

    protected function setUp(): void
    {
        $this->getTripByIdTask = $this->createMock(GetTripByIdTask::class);
        $this->getPatternWithRouteByPatternIdTask = $this->createMock(GetPatternWithRouteByPatternIdTask::class);
        $this->findStopsByIdsTask = $this->createMock(GetStopsByIdsTask::class);
        $this->cache = $this->createMock(TripCache::class);
        $this->action = new GetTripAction(
            $this->getTripByIdTask,
            $this->getPatternWithRouteByPatternIdTask,
            $this->findStopsByIdsTask,
            $this->cache,
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

        $trip = Trip::create(
            patternId: $pattern->id,
            serviceDays: ServiceDays::everyday(),
        );
        $this->setEntityId($trip, $tripId);

        $stopTime = $trip->createStopTime(
            stopSequence: 1,
            stopId: $stop->id,
            arrivalTime: TimeOfDay::fromSeconds(61200),
            isEstimate: true,
        );

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturnCallback(function (string $key, callable $callback) use ($trip) {
                $this->assertEquals("trip.$trip->id", $key);

                return $callback($trip);
            });

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

        $stopForTripDTOs = StopForTripDTO::create(
            stop: StopDTO::create($stop),
            stopTime: $stopTime,
        );

        $tripDetailsDTO = TripDetailsDTO::create(
            trip: $trip,
            pattern: $pattern,
            route: RouteDTO::create($pattern->route),
            stops: [$stopForTripDTOs],
        );

        $input = new GetTripInput($tripId, TimeOfDay::now());

        $stops = array_map(
            fn(StopForTripDTO $stop) => $stop->withIsPassed($input->time),
            $tripDetailsDTO->stops,
        );

        $expected  = $tripDetailsDTO->withStops($stops);
        $result = $this->action->run($input);
        $this->assertEquals($expected, $result);
    }
}
