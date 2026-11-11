<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Tasks\Route;

use App\Application\Tasks\Route\DeleteRouteTask;
use App\Application\Tasks\Trip\FindTripsByPatternIdsTask;
use App\Domain\Entity\Route\Route;
use App\Domain\Entity\Trip\Trip;
use App\Domain\Enums\Direction;
use App\Domain\Repositories\Route\RouteRepositoryInterface;
use App\Domain\Repositories\Trip\TripRepositoryInterface;
use App\Domain\ValueObjects\ServiceDays;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class DeleteRouteTaskTest extends AbstractTestCase
{
    private FindTripsByPatternIdsTask|MockObject $findTripsByPatternIdsTask;
    private TripRepositoryInterface|MockObject $tripRepository;
    private RouteRepositoryInterface|MockObject $routeRepository;
    private DeleteRouteTask $task;

    protected function setUp(): void
    {
        $this->findTripsByPatternIdsTask = $this->createMock(FindTripsByPatternIdsTask::class);
        $this->tripRepository = $this->createMock(TripRepositoryInterface::class);
        $this->routeRepository = $this->createMock(RouteRepositoryInterface::class);
        $this->task = new DeleteRouteTask(
            $this->findTripsByPatternIdsTask,
            $this->tripRepository,
            $this->routeRepository,
        );
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
        $pattern = $route->createPattern(Direction::FORWARD, 'test');
        $this->setEntityId($pattern, $patternId);

        $tripId = 1;
        $trip = Trip::create(
            patternId: $pattern->id,
            serviceDays: ServiceDays::everyday(),
        );
        $this->setEntityId($trip, $tripId);

        $this->findTripsByPatternIdsTask->expects($this->once())
            ->method('run')
            ->with([$patternId])
            ->willReturn([$trip]);

        $this->tripRepository->expects($this->once())
            ->method('remove')
            ->with($trip);

        $this->routeRepository->expects($this->once())
            ->method('remove')
            ->with($route);

        $this->tripRepository->expects($this->never())->method('commit');
        $this->routeRepository->expects($this->never())->method('commit');

        $this->task->run($route);
    }

    /**
     * @throws ReflectionException
     */
    public function testDeletesRouteWithoutPatterns(): void
    {
        $route = Route::create(shortName: '1', longName: 'test');
        $this->setEntityId($route, 1);

        $this->findTripsByPatternIdsTask->expects($this->once())
            ->method('run')
            ->with([])
            ->willReturn([]);

        $this->tripRepository->expects($this->never())->method('remove');
        $this->tripRepository->expects($this->never())->method('commit');

        $this->routeRepository->expects($this->once())
            ->method('remove')
            ->with($route);
        $this->routeRepository->expects($this->never())->method('commit');

        $this->task->run($route);
    }
}
