<?php

declare(strict_types=1);

namespace App\Tests\Unit\Actions\Route;

use App\Actions\Route\GetRouteAction;
use App\Caching\Interfaces\CacheInterface;
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
    private CacheInterface|MockObject $cache;
    private GetRouteAction $getRouteAction;

    public function setUp(): void
    {
        $this->getRouteTask = $this->createMock(GetRouteTask::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->getRouteAction = new GetRouteAction(
            $this->getRouteTask,
            $this->cache,
            $ttlMinutes = 3600,
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

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturnCallback(function (string $key, int $ttlMinutes, callable $callback) use ($route) {
                $this->assertEquals("routes.$route->id", $key);

                return $callback();
            });

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
