<?php

declare(strict_types=1);

namespace App\Tests\Unit\Actions\Stop;

use App\Actions\Stop\GetStopAction;
use App\Caching\StopCache;
use App\Data\Entity\Stop;
use App\Data\ValueObjects\Coordinates;
use App\DTOs\StopDTO;
use App\Tasks\Stop\GetStopByIdTask;
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
