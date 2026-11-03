<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Tasks\Pattern;

use App\Application\Tasks\Pattern\GetPatternWithRouteByPatternIdTask;
use App\Domain\Entity\Route\Route;
use App\Domain\Enums\Direction;
use App\Domain\Repositories\PatternRepositoryInterface;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class GetPatternWithRouteByPatternIdTaskTest extends AbstractTestCase
{
    private PatternRepositoryInterface|MockObject $repository;
    private GetPatternWithRouteByPatternIdTask $task;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(PatternRepositoryInterface::class);
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
