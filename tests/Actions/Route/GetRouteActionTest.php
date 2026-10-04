<?php

declare(strict_types=1);

namespace App\Tests\Actions\Route;

use App\Actions\Route\GetRouteAction;
use App\Data\Entity\Route\Pattern;
use App\Data\Entity\Route\Route;
use App\DTOs\PatternDTO;
use App\DTOs\RouteWithPatternsDTO;
use App\Enum\Direction;
use App\Tasks\Route\GetRouteTask;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class GetRouteActionTest extends AbstractTestCase
{
    private GetRouteTask|MockObject $getRouteTask;
    private GetRouteAction $getRouteAction;

    public function setUp(): void
    {
        $this->getRouteTask = $this->createMock(GetRouteTask::class);
        $this->getRouteAction = new GetRouteAction(
            $this->getRouteTask,
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
        $pattern = Pattern::create(
            route: $route,
            direction: Direction::FORWARD,
            name: 'test',
        );
        $this->setEntityId($pattern, $patternId);

        $route->addPattern($pattern);

        $this->getRouteTask->expects($this->once())
            ->method('run')
            ->willReturn($route);

        $patternDTO = new PatternDTO(
            id: $pattern->id,
            direction: $pattern->direction,
            name: $pattern->name,
        );

        $expected = new RouteWithPatternsDTO(
            id: $routeId,
            shortName: $route->shortName,
            longName: $route->longName,
            parameters: [$patternDTO],
        );

        $result = $this->getRouteAction->run($routeId);
        $this->assertEquals($expected, $result);
    }
}
