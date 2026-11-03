<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Actions\Route;

use App\Application\Actions\Route\GetRouteAction;
use App\Application\Caching\RouteCache;
use App\Application\DTOs\PatternDTO;
use App\Application\DTOs\RouteWithPatternsDTO;
use App\Application\Tasks\Route\GetRouteTask;
use App\Domain\Entity\Route\Route;
use App\Domain\Enums\Direction;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class GetRouteActionTest extends AbstractTestCase
{
    private GetRouteTask|MockObject $getRouteTask;
    private RouteCache|MockObject $cache;
    private GetRouteAction $getRouteAction;

    public function setUp(): void
    {
        $this->getRouteTask = $this->createMock(GetRouteTask::class);
        $this->cache = $this->createMock(RouteCache::class);
        $this->getRouteAction = new GetRouteAction(
            $this->getRouteTask,
            $this->cache,
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
        $pattern = $route->createPattern(Direction::FORWARD, 'test');
        $this->setEntityId($pattern, $patternId);

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturnCallback(function (string $key, callable $callback) use ($route) {
                $this->assertEquals("routes.$route->id", $key);

                return $callback();
            });

        $this->getRouteTask->expects($this->once())
            ->method('run')
            ->willReturn($route);

        $patternDTO = PatternDTO::create($pattern);

        $expected = RouteWithPatternsDTO::create(route: $route, patterns: [$patternDTO]);

        $result = $this->getRouteAction->run($routeId);
        $this->assertEquals($expected, $result);
    }
}
