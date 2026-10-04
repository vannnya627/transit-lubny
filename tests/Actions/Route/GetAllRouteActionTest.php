<?php

declare(strict_types=1);

namespace App\Tests\Actions\Route;

use App\Actions\Route\GetAllRouteAction;
use App\Data\Entity\Route\Route;
use App\DTOs\RouteDTO;
use App\Tasks\Route\GetAllRouteTask;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class GetAllRouteActionTest extends AbstractTestCase
{
    private GetAllRouteTask|MockObject $getAllRouteTask;
    private GetAllRouteAction $getAllRouteAction;

    public function setUp(): void
    {
        $this->getAllRouteTask = $this->createMock(GetAllRouteTask::class);
        $this->getAllRouteAction = new GetAllRouteAction(
            $this->getAllRouteTask,
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testRun(): void
    {
        $routeId = 1;
        $route = Route::create(shortName: '1', longName: 'test');
        $this->setEntityId($route, $routeId);

        $this->getAllRouteTask->expects($this->once())
            ->method('run')
            ->willReturn([$route]);

        $expected = new RouteDTO(
            id: $route->id,
            shortName: $route->shortName,
            longName: $route->longName,
        );

        $result = $this->getAllRouteAction->run();
        $this->assertEquals([$expected], $result);
    }

    public function testRunWhenRouteDoesNotExist(): void
    {
        $this->getAllRouteTask->expects($this->once())
            ->method('run')
            ->willReturn([]);

        $result = $this->getAllRouteAction->run();
        $this->assertEquals([], $result);
    }
}
