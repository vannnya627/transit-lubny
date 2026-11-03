<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Actions\Stop;

use App\Application\Actions\Stop\GetStopAction;
use App\Application\Caching\StopCache;
use App\Application\DTOs\StopDTO;
use App\Application\Tasks\Stop\GetStopByIdTask;
use App\Domain\Entity\Stop;
use App\Domain\ValueObjects\Coordinates;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class GetStopActionTest extends AbstractTestCase
{
    private GetStopByIdTask|MockObject $getStopByIdTask;
    private StopCache|MockObject $cache;
    private GetStopAction $action;

    protected function setUp(): void
    {
        $this->getStopByIdTask = $this->createMock(GetStopByIdTask::class);
        $this->cache = $this->createMock(StopCache::class);
        $this->action = new GetStopAction(
            $this->getStopByIdTask,
            $this->cache,
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testRun(): void
    {
        $stopId = 1;
        $stop = Stop::create(
            name: 'test',
            coordinates: Coordinates::create(50.0, 32.0),
            cityCode: 1,
        );
        $this->setEntityId($stop, $stopId);

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturnCallback(function (string $key, callable $callback) use ($stop) {
                $this->assertEquals("stop.$stop->id", $key);

                return $callback();
            });

        $this->getStopByIdTask->expects($this->once())
            ->method('run')
            ->with($stopId)
            ->willReturn($stop);

        $expected = StopDTO::create($stop);
        $result = $this->action->run($stopId);
        $this->assertEquals($expected, $result);
    }
}
