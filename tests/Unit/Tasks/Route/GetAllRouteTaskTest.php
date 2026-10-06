<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tasks\Route;

use App\Data\Entity\Route\Route;
use App\Data\Repository\Interfaces\RouteRepositoryInterface;
use App\Tasks\Route\GetAllRouteTask;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class GetAllRouteTaskTest extends AbstractTestCase
{
    private RouteRepositoryInterface|MockObject $repository;
    private GetAllRouteTask $task;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(RouteRepositoryInterface::class);
        $this->task = new GetAllRouteTask($this->repository);
    }

    /**
     * @throws ReflectionException
     */
    public function testRun()
    {
        $routeId = 1;

        $route = Route::create(shortName: '1', longName: 'test');
        $this->setEntityId($route, $routeId);

        $this->repository->expects($this->once())
            ->method('getAllRoutes')
            ->willReturn([$route]);

        $result = $this->task->run();
        $this->assertEquals([$route], $result);
    }
}
