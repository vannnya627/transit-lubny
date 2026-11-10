<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Tasks\StopTime;

use App\Application\DTOs\Inputs\GetStopDeparturesInput;
use App\Application\Tasks\StopTime\FindStopTimesWithTripTask;
use App\Domain\Entity\Route\Route;
use App\Domain\Entity\Trip\Trip;
use App\Domain\Enums\Direction;
use App\Domain\Repositories\Trip\StopTimeReadRepositoryInterface;
use App\Domain\ValueObjects\ServiceDays;
use App\Domain\ValueObjects\TimeOfDay;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class FindStopTimesWithTripTaskTest extends AbstractTestCase
{
    private StopTimeReadRepositoryInterface|MockObject $repository;
    private FindStopTimesWithTripTask $task;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(StopTimeReadRepositoryInterface::class);
        $this->task = new FindStopTimesWithTripTask($this->repository);
    }

    /**
     * @throws ReflectionException
     */
    public function testRun(): void
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

        $stopTime = $trip->createStopTime(
            stopSequence: 1,
            stopId: $input->stopId,
            arrivalTime: TimeOfDay::fromSeconds(61200),
            isEstimate: true,
        );

        $this->repository->expects($this->once())
            ->method('findStopTimesWithTrip')
            ->with(stopId: $input->stopId, time: $input->time, limit: $input->limit)
            ->willReturn([$stopTime]);

        $result = $this->task->run($input);
        $this->assertEquals([$stopTime], $result);
    }
}
