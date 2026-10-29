<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tasks\Route;

use App\Data\Entity\Route\Route;
use App\Data\Entity\Stop;
use App\Data\Repository\Interfaces\RouteRepositoryInterface;
use App\Data\ValueObjects\Coordinates;
use App\Enum\Direction;
use App\Tasks\Route\FindPatternsWithRoutesByIdsTask;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class FindPatternsWithRoutesByIdsTaskTest extends AbstractTestCase
{
    private RouteRepositoryInterface|MockObject $routeRepository;
    private FindPatternsWithRoutesByIdsTask $task;

    protected function setUp(): void
    {
        $this->routeRepository = $this->createMock(RouteRepositoryInterface::class);
        $this->task = new FindPatternsWithRoutesByIdsTask($this->routeRepository);
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

        $stopId = 1;
        $stop = Stop::create(
            name: 'test',
            coordinates: Coordinates::create(50.0, 32.0),
            cityCode: 1,
        );
        $this->setEntityId($stop, $stopId);

        $patternStop = $pattern->createPatternStop(1, $stop->id);

        $this->routeRepository->expects($this->once())
            ->method('findPatternsWithRoutesByIds')
            ->with([$pattern->id])
            ->willReturn([$pattern]);

        $result = $this->task->run([$pattern->id]);
        $this->assertEquals([$pattern], $result);

    }
}
