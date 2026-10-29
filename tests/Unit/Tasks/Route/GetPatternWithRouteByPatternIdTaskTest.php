<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tasks\Route;

use App\Data\Entity\Route\Route;
use App\Data\Repository\Interfaces\RouteRepositoryInterface;
use App\Enum\Direction;
use App\Tasks\Route\GetPatternWithRouteByPatternIdTask;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class GetPatternWithRouteByPatternIdTaskTest extends AbstractTestCase
{
    private RouteRepositoryInterface|MockObject $repository;
    private GetPatternWithRouteByPatternIdTask $task;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(RouteRepositoryInterface::class);
        $this->task = new GetPatternWithRouteByPatternIdTask($this->repository);
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

        $this->repository->expects($this->once())
            ->method('getPatternWithRouteById')
            ->with($pattern->id)
            ->willReturn($pattern);

        $result = $this->task->run($patternId);
        $this->assertEquals($pattern, $result);
    }
}
