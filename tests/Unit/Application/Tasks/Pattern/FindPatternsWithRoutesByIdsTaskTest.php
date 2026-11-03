<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Tasks\Pattern;

use App\Application\Tasks\Pattern\FindPatternsWithRoutesByIdsTask;
use App\Domain\Entity\Route\Route;
use App\Domain\Entity\Stop;
use App\Domain\Enums\Direction;
use App\Domain\Repositories\PatternRepositoryInterface;
use App\Domain\ValueObjects\Coordinates;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class FindPatternsWithRoutesByIdsTaskTest extends AbstractTestCase
{
    private PatternRepositoryInterface|MockObject $patternRepository;
    private FindPatternsWithRoutesByIdsTask $task;

    protected function setUp(): void
    {
        $this->patternRepository = $this->createMock(PatternRepositoryInterface::class);
        $this->task = new FindPatternsWithRoutesByIdsTask($this->patternRepository);
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

        $this->patternRepository->expects($this->once())
            ->method('findPatternsWithRoutesByIds')
            ->with([$pattern->id])
            ->willReturn([$pattern]);

        $result = $this->task->run([$pattern->id]);
        $this->assertEquals([$pattern], $result);

    }
}
