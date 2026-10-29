<?php

declare(strict_types=1);

namespace App\Tests\Unit\Tasks\Route;

use App\Data\Entity\Route\Route;
use App\Data\Repository\Interfaces\RouteRepositoryInterface;
use App\Tasks\Route\FindRouteByShortNameTask;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class FindRouteByShortNameTaskTest extends AbstractTestCase
{
    private RouteRepositoryInterface|MockObject $repository;
    private FindRouteByShortNameTask $task;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(RouteRepositoryInterface::class);
        $this->task = new FindRouteByShortNameTask($this->repository);
    }

    /**
     * @throws ReflectionException
     */
    public function testRun()
    {
        $shortName = '2';

        $routeId = 1;
        $route = Route::create(shortName: $shortName, longName: 'test');
        $this->setEntityId($route, $routeId);

        $this->repository->expects($this->once())
            ->method('findRouteByShortName')
            ->with($shortName)
            ->willReturn($route);

        $result = $this->task->run($shortName);
        $this->assertEquals($route, $result);
    }
}
