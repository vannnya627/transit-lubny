<?php

declare(strict_types=1);

namespace App\Tests\Tasks\Trip;

use App\Data\Entity\Trip\Trip;
use App\Data\Repository\Interfaces\TripRepositoryInterface;
use App\Data\ValueObjects\ServiceDays;
use App\Tasks\Trip\GetTripByIdTask;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;

#[AllowMockObjectsWithoutExpectations]
class GetTripByIdTaskTest extends AbstractTestCase
{
    private TripRepositoryInterface|MockObject $repository;
    private GetTripByIdTask $task;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(TripRepositoryInterface::class);
        $this->task = new GetTripByIdTask($this->repository);
    }

    /**
     * @throws /ReflectionException
     */
    public function testRun()
    {
        $tripId = 1;
        $trip = Trip::create(
            patternId: 1,
            serviceDays: ServiceDays::everyday(),
        );
        $this->setEntityId($trip, $tripId);

        $this->repository->expects($this->once())
            ->method('getTripById')
            ->with($tripId)
            ->willReturn($trip);

        $result = $this->task->run($tripId);
        $this->assertEquals($trip, $result);
    }
}
