<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tasks\Trip;

use App\Data\Entity\Route\Route;
use App\Data\Entity\Trip\Trip;
use App\Data\Repository\Interfaces\TripRepositoryInterface;
use App\Data\ValueObjects\ServiceDays;
use App\DTOs\Input\GetStopDeparturesInput;
use App\Enum\Direction;
use App\Tasks\Trip\FindTripsWithStopTimesForStopTask;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class FindTripsWithStopTimesForStopTaskTest extends AbstractTestCase
{
    private TripRepositoryInterface|MockObject $repository;
    private FindTripsWithStopTimesForStopTask $task;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(TripRepositoryInterface::class);
        $this->task = new FindTripsWithStopTimesForStopTask($this->repository);
    }

    /**
     * @throws ReflectionException
     */
    public function testRun()
    {
        $input = new GetStopDeparturesInput(
            1,
        );
        $routeId = 1;
        $route = Route::create(shortName: '1', longName: 'test');
        $this->setEntityId($route, $routeId);

        $patternId = 1;
        $pattern = $route->createPattern(Direction::FORWARD, 'test');
        $this->setEntityId($pattern, $patternId);

        $patternStop = $pattern->createPatternStop(1, 1);

        $tripId = 1;
        $trip = Trip::create(
            patternId: $pattern->id,
            serviceDays: ServiceDays::everyday(),
        );
        $this->setEntityId($trip, $tripId);

        $this->repository->expects($this->once())
            ->method('findTripsWithStopTimesForStop')
            ->with($input)
            ->willReturn([$trip]);

        $result = $this->task->run($input);
        $this->assertEquals([$trip], $result);
    }
}
