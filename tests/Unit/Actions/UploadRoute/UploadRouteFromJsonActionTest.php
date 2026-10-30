<?php

declare(strict_types=1);

namespace App\Tests\Unit\Actions\UploadRoute;

use App\Actions\UploadRoute\UploadRouteFromJsonAction;
use App\Data\Entity\Route\Route;
use App\Data\Entity\Stop;
use App\Data\Entity\Trip\Trip;
use App\Data\Repository\Interfaces\RouteRepositoryInterface;
use App\Data\Repository\Interfaces\StopRepositoryInterface;
use App\Data\Repository\Interfaces\TransactionInterface;
use App\Data\Repository\Interfaces\TripRepositoryInterface;
use App\Data\ValueObjects\Coordinates;
use App\Data\ValueObjects\ServiceDays;
use App\Enum\Direction;
use App\Tasks\Route\FindRouteByShortNameTask;
use App\Tasks\Stop\FindStopsByCityCodesTask;
use App\Tasks\Trip\FindTripsByPatternIdsTask;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;
use Throwable;

#[AllowMockObjectsWithoutExpectations]
class UploadRouteFromJsonActionTest extends AbstractTestCase
{
    private StopRepositoryInterface|MockObject $stopRepository;
    private RouteRepositoryInterface|MockObject $routeRepository;
    private TripRepositoryInterface|MockObject $tripRepository;
    private FindTripsByPatternIdsTask|MockObject $findTripsByPatternIdsTask;
    private FindRouteByShortNameTask|MockObject $findRouteByShortNameTask;
    private FindStopsByCityCodesTask|MockObject $findStopsByCityCodesTask;
    private TransactionInterface|MockObject $transaction;

    private UploadRouteFromJsonAction $action;

    private const string TEST_FILE = 'tests/data/route_test.json';

    protected function setUp(): void
    {
        $this->stopRepository = $this->createMock(StopRepositoryInterface::class);
        $this->routeRepository = $this->createMock(RouteRepositoryInterface::class);
        $this->tripRepository = $this->createMock(TripRepositoryInterface::class);
        $this->findTripsByPatternIdsTask = $this->createMock(FindTripsByPatternIdsTask::class);
        $this->findRouteByShortNameTask = $this->createMock(FindRouteByShortNameTask::class);
        $this->findStopsByCityCodesTask = $this->createMock(FindStopsByCityCodesTask::class);
        $this->transaction = $this->createMock(TransactionInterface::class);

        $this->action = new UploadRouteFromJsonAction(
            $this->stopRepository,
            $this->routeRepository,
            $this->tripRepository,
            $this->findTripsByPatternIdsTask,
            $this->findRouteByShortNameTask,
            $this->findStopsByCityCodesTask,
            $this->transaction,
        );
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testRun(): void
    {
        $stopId1 = 1;
        $stop1 = Stop::create('Зупинка 1', Coordinates::create(50.042642, 32.942818), 991);
        $this->setEntityId($stop1, $stopId1);

        $stopId2 = 2;
        $stop2 = Stop::create('Зупинка 2', Coordinates::create(50.038582, 32.940063), 992);
        $this->setEntityId($stop2, $stopId2);

        $this->transaction->expects($this->once())
            ->method('executeInTransaction')
            ->willReturnCallback(fn(callable $operation) => $operation());

        $this->findRouteByShortNameTask->expects($this->once())
            ->method('run')
            ->with('99')
            ->willReturn(null);

        $this->routeRepository->expects($this->once())
            ->method('commit');

        $this->findStopsByCityCodesTask->expects($this->once())
            ->method('run')
            ->with([991, 992])
            ->willReturn([$stop1, $stop2]);

        $this->stopRepository->expects($this->once())
            ->method('commit');

        $this->routeRepository->expects($this->once())
            ->method('saveAndCommit')
            ->willReturnCallback(function (Route $route): void {
                $this->assertSame('99', $route->shortName);
                $this->assertCount(1, $route->patterns);

                $this->setEntityId($route, 1);
                foreach ($route->patterns as $index => $pattern) {
                    $this->setEntityId($pattern, $index + 1);
                }
            });

        $this->tripRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Trip $trip): bool {
                $this->assertSame(1, $trip->patternId);
                $this->assertCount(2, $trip->stopTimes);

                return true;
            }));

        $this->tripRepository->expects($this->once())
            ->method('commit');

        $this->action->run(self::TEST_FILE);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testRunWhenStopsDoNotExist(): void
    {
        $this->transaction->expects($this->once())
            ->method('executeInTransaction')
            ->willReturnCallback(fn(callable $operation) => $operation());

        $this->findRouteByShortNameTask->expects($this->once())
            ->method('run')
            ->with('99')
            ->willReturn(null);

        $this->routeRepository->expects($this->once())
            ->method('commit');

        $this->findStopsByCityCodesTask->expects($this->once())
            ->method('run')
            ->with([991, 992])
            ->willReturn([]);

        $this->stopRepository->expects($this->exactly(2))
            ->method('save')
            ->willReturnCallback(function (Stop $stop): void {
                $this->setEntityId($stop, $stop->cityCode);
            });

        $this->stopRepository->expects($this->once())
            ->method('commit');

        $this->routeRepository->expects($this->once())
            ->method('saveAndCommit')
            ->willReturnCallback(function (Route $route): void {
                $this->setEntityId($route, 1);
                foreach ($route->patterns as $index => $pattern) {
                    $this->setEntityId($pattern, $index + 1);
                }
            });

        $this->tripRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Trip::class));

        $this->tripRepository->expects($this->once())
            ->method('commit');

        $this->action->run(self::TEST_FILE);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testRunWhenDuplicateRouteExists(): void
    {
        $existingRouteId = 10;
        $existingRoute = Route::create('99', 'Old Route');
        $this->setEntityId($existingRoute, $existingRouteId);

        $existingPatternId = 20;
        $existingPattern = $existingRoute->createPattern(Direction::FORWARD, 'Old Pattern');
        $this->setEntityId($existingPattern, $existingPatternId);

        $existingTripId = 30;
        $existingTrip = Trip::create($existingPatternId, ServiceDays::everyday());
        $this->setEntityId($existingTrip, $existingTripId);

        $stopId1 = 1;
        $stop1 = Stop::create('Зупинка 1', Coordinates::create(50.042642, 32.942818), 991);
        $this->setEntityId($stop1, $stopId1);

        $stopId2 = 2;
        $stop2 = Stop::create('Зупинка 2', Coordinates::create(50.038582, 32.940063), 992);
        $this->setEntityId($stop2, $stopId2);

        $this->transaction->expects($this->once())
            ->method('executeInTransaction')
            ->willReturnCallback(fn(callable $operation) => $operation());

        $this->findRouteByShortNameTask->expects($this->once())
            ->method('run')
            ->with('99')
            ->willReturn($existingRoute);

        $this->findTripsByPatternIdsTask->expects($this->once())
            ->method('run')
            ->with([$existingPatternId])
            ->willReturn([$existingTrip]);

        $this->tripRepository->expects($this->once())
            ->method('remove')
            ->with($existingTrip);

        $this->routeRepository->expects($this->once())
            ->method('remove')
            ->with($existingRoute);

        $this->routeRepository->expects($this->once())
            ->method('commit');

        $this->findStopsByCityCodesTask->expects($this->once())
            ->method('run')
            ->with([991, 992])
            ->willReturn([$stop1, $stop2]);

        $this->stopRepository->expects($this->once())
            ->method('commit');

        $this->routeRepository->expects($this->once())
            ->method('saveAndCommit')
            ->willReturnCallback(function (Route $route): void {
                $this->setEntityId($route, 1);
                foreach ($route->patterns as $index => $pattern) {
                    $this->setEntityId($pattern, $index + 1);
                }
            });

        $this->tripRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Trip::class));

        $this->tripRepository->expects($this->once())
            ->method('commit');

        $this->action->run(self::TEST_FILE);
    }
}
