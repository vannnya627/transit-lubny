<?php

declare(strict_types=1);

namespace App\Tests\Tasks\Trip;

use App\Data\Entity\Route\Pattern;
use App\Data\Entity\Route\PatternStop;
use App\Data\Entity\Route\Route;
use App\Data\Entity\Trip\Trip;
use App\Data\Repository\Interfaces\TripRepositoryInterface;
use App\Data\ValueObjects\ServiceDays;
use App\Enum\Direction;
use App\Tasks\Trip\FindTripsWithStopTimesByPatternIdTask;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class FindTripsWithStopTimesByPatternIdTaskTest extends AbstractTestCase
{
    private TripRepositoryInterface|MockObject $repository;
    private FindTripsWithStopTimesByPatternIdTask $task;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(TripRepositoryInterface::class);
        $this->task = new FindTripsWithStopTimesByPatternIdTask($this->repository);
    }

    /**
     * @throws ReflectionException
     */
    public function testRun()
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

        $patternStop = PatternStop::create(
            pattern: $pattern,
            stopSequence: 1,
            stopId: 1,
        );
        $pattern->addPatternStop($patternStop);

        $tripId = 1;
        $trip = Trip::create(
            patternId: $pattern->id,
            serviceDays: ServiceDays::everyday(),
        );
        $this->setEntityId($trip, $tripId);

        $this->repository->expects($this->once())
            ->method('findTripsWithStopTimes')
            ->with($patternId)
            ->willReturn([$trip]);

        $result = $this->task->run($patternId);
        $this->assertEquals([$trip], $result);
    }
}
