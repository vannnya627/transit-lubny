<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Actions\UploadRoute;

use App\Application\Actions\UploadRoute\UploadRouteFromJsonAction;
use App\Application\Events\RouteImportCompletedEvent;
use App\Application\Import\DTOs\PatternImportData;
use App\Application\Import\DTOs\RouteImportData;
use App\Application\Import\DTOs\StopImportData;
use App\Application\Import\DTOs\TripImportData;
use App\Application\Import\Exceptions\InvalidRouteImportException;
use App\Application\Import\Interfaces\RouteImportFileReaderInterface;
use App\Application\Import\Interfaces\RouteImportParserInterface;
use App\Application\Tasks\Route\DeleteRouteTask;
use App\Application\Tasks\Route\FindRouteByShortNameTask;
use App\Application\Tasks\Stop\FindStopsByCityCodesTask;
use App\Domain\Entity\Route\Route;
use App\Domain\Entity\Stop;
use App\Domain\Entity\Trip\Trip;
use App\Domain\Enums\Direction;
use App\Domain\Repositories\Route\RouteRepositoryInterface;
use App\Domain\Repositories\StopRepositoryInterface;
use App\Domain\Repositories\TransactionInterface;
use App\Domain\Repositories\Trip\TripRepositoryInterface;
use App\Domain\ValueObjects\Coordinates;
use App\Domain\ValueObjects\ServiceDays;
use App\Domain\ValueObjects\TimeOfDay;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionException;
use Throwable;

#[AllowMockObjectsWithoutExpectations]
class UploadRouteFromJsonActionTest extends AbstractTestCase
{
    private StopRepositoryInterface|MockObject $stopRepository;
    private RouteRepositoryInterface|MockObject $routeRepository;
    private TripRepositoryInterface|MockObject $tripRepository;
    private FindRouteByShortNameTask|MockObject $findRouteByShortNameTask;
    private FindStopsByCityCodesTask|MockObject $findStopsByCityCodesTask;
    private TransactionInterface|MockObject $transaction;
    private DeleteRouteTask|MockObject $deleteRouteTask;
    private EventDispatcherInterface|MockObject $dispatcher;
    private RouteImportFileReaderInterface|MockObject $fileReader;
    private RouteImportParserInterface|MockObject $parser;

    private UploadRouteFromJsonAction $action;

    private const string TEST_FILE = 'tests/data/route_test.json';

    protected function setUp(): void
    {
        $this->stopRepository = $this->createMock(StopRepositoryInterface::class);
        $this->routeRepository = $this->createMock(RouteRepositoryInterface::class);
        $this->tripRepository = $this->createMock(TripRepositoryInterface::class);
        $this->findRouteByShortNameTask = $this->createMock(FindRouteByShortNameTask::class);
        $this->findStopsByCityCodesTask = $this->createMock(FindStopsByCityCodesTask::class);
        $this->transaction = $this->createMock(TransactionInterface::class);
        $this->deleteRouteTask = $this->createMock(DeleteRouteTask::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->fileReader = $this->createMock(RouteImportFileReaderInterface::class);
        $this->parser = $this->createMock(RouteImportParserInterface::class);

        $this->fileReader->method('read')->willReturn('[]');

        $this->action = new UploadRouteFromJsonAction(
            $this->stopRepository,
            $this->routeRepository,
            $this->tripRepository,
            $this->findRouteByShortNameTask,
            $this->findStopsByCityCodesTask,
            $this->transaction,
            $this->deleteRouteTask,
            $this->dispatcher,
            $this->fileReader,
            $this->parser,
        );
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testRun(): void
    {

        $this->parser->expects($this->once())->method('parse')
            ->with('[]', self::TEST_FILE)
            ->willReturn([$this->createImportData()]);

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

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(new RouteImportCompletedEvent())
            ->willReturnArgument(0);

        $this->action->run(self::TEST_FILE);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testRunWhenStopsDoNotExist(): void
    {

        $this->parser->expects($this->once())->method('parse')
            ->with('[]', self::TEST_FILE)
            ->willReturn([$this->createImportData()]);

        $this->transaction->expects($this->once())
            ->method('executeInTransaction')
            ->willReturnCallback(fn(callable $operation) => $operation());

        $this->findRouteByShortNameTask->expects($this->once())
            ->method('run')
            ->with('99')
            ->willReturn(null);

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

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(new RouteImportCompletedEvent())
            ->willReturnArgument(0);

        $this->action->run(self::TEST_FILE);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testRunWhenDuplicateRouteExists(): void
    {

        $this->parser->expects($this->once())->method('parse')
            ->with('[]', self::TEST_FILE)
            ->willReturn([$this->createImportData()]);

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

        $this->deleteRouteTask->expects($this->once())
            ->method('run')
            ->with($existingRoute);

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

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(new RouteImportCompletedEvent())
            ->willReturnArgument(0);

        $this->action->run(self::TEST_FILE);
    }

    /**
     * @throws Throwable
     */
    public function testRunDoesNotStartTransactionWhenParsingFails(): void
    {
        $this->parser->method('parse')
            ->willThrowException(InvalidRouteImportException::at(self::TEST_FILE, '$', 'bad json'));

        $this->transaction->expects($this->never())->method('executeInTransaction');
        $this->dispatcher->expects($this->never())->method('dispatch');

        $this->expectException(InvalidRouteImportException::class);

        $this->action->run(self::TEST_FILE);
    }

    private function createImportData(): RouteImportData
    {
        return new RouteImportData(
            '99',
            'Тестовий маршрут',
            [
                new PatternImportData(
                    Direction::FORWARD,
                    'Прямо',
                    [
                        new StopImportData(991, 'Зупинка 1', Coordinates::create(50.042642, 32.942818)),
                        new StopImportData(992, 'Зупинка 2', Coordinates::create(50.038582, 32.940063)),
                    ],
                    [
                        new TripImportData([
                            TimeOfDay::fromString('08:00'),
                            TimeOfDay::fromString('08:15'),
                        ]),
                    ],
                ),
            ],
        );
    }
}
