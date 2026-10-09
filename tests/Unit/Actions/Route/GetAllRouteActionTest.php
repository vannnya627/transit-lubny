<?php

declare(strict_types=1);

namespace App\Tests\Unit\Actions\Route;

use App\Actions\Route\GetAllRouteAction;
use App\Caching\RouteCache;
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
    private RouteCache|MockObject $cache;
    private GetAllRouteAction $getAllRouteAction;

    public function setUp(): void
    {
        $this->getAllRouteTask = $this->createMock(GetAllRouteTask::class);
        $this->cache = $this->createMock(RouteCache::class);


        $this->getAllRouteAction = new GetAllRouteAction(
            $this->getAllRouteTask,
            $this->cache,
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

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturnCallback(function (string $key, callable $callback) {
                $this->assertEquals('routes', $key);

                return $callback();
            });

        $this->getAllRouteTask->expects($this->once())
            ->method('run')
            ->willReturn([$route]);

        $expected = RouteDTO::create($route);

        $result = $this->getAllRouteAction->run();
        $this->assertEquals([$expected], $result);
    }

    public function testRunWhenRouteDoesNotExist(): void
    {
        $this->cache->expects($this->once())
            ->method('get')
            ->willReturnCallback(function (string $key, callable $callback) {
                $this->assertEquals('routes', $key);

                return $callback();
            });

        $this->getAllRouteTask->expects($this->once())
            ->method('run')
            ->willReturn([]);

        $result = $this->getAllRouteAction->run();
        $this->assertEquals([], $result);
    }
}
