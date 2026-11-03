<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Tasks\Trip;

use App\Application\DTOs\Inputs\GetStopDeparturesInput;
use App\Application\Tasks\Trip\FindTripsWithStopTimesForStopTask;
use App\Domain\Entity\Route\Route;
use App\Domain\Entity\Trip\Trip;
use App\Domain\Enums\Direction;
use App\Domain\Repositories\TripRepositoryInterface;
use App\Domain\ValueObjects\ServiceDays;
use App\Domain\ValueObjects\TimeOfDay;
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
            TimeOfDay::now(),
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
            ->with(stopId: $input->stopId, time: $input->time, limit: $input->limit)
            ->willReturn([$trip]);

        $result = $this->task->run($input);
        $this->assertEquals([$trip], $result);
    }
}
