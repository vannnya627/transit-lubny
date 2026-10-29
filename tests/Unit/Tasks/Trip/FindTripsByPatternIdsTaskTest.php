<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tasks\Trip;

use App\Data\Entity\Route\Route;
use App\Data\Entity\Trip\Trip;
use App\Data\Repository\Interfaces\TripRepositoryInterface;
use App\Data\ValueObjects\ServiceDays;
use App\Enum\Direction;
use App\Tasks\Trip\FindTripsByPatternIdsTask;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;

#[AllowMockObjectsWithoutExpectations]
class FindTripsByPatternIdsTaskTest extends AbstractTestCase
{
    private TripRepositoryInterface|MockObject $repository;
    private FindTripsByPatternIdsTask $task;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(TripRepositoryInterface::class);
        $this->task = new FindTripsByPatternIdsTask($this->repository);
    }

    /**
     * @throws /ReflectionException
     */
    public function testRun()
    {

        $routeId = 1;

        $route = Route::create(shortName: '1', longName: 'test');
        $this->setEntityId($route, $routeId);

        $patternId = 1;
        $pattern = $route->createPattern(Direction::FORWARD, 'test');
        $this->setEntityId($pattern, $patternId);

        $tripId = 1;
        $trip = Trip::create(
            patternId: $pattern->id,
            serviceDays: ServiceDays::everyday(),
        );
        $this->setEntityId($trip, $tripId);


        $this->repository->expects($this->once())
            ->method('findTrips')
            ->with([$patternId])
            ->willReturn([$trip]);

        $result = $this->task->run([$patternId]);
        $this->assertEquals([$trip], $result);
    }
}
