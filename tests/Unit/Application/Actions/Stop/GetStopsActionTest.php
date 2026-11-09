<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Actions\Stop;

use App\Application\Actions\Stop\GetStopsAction;
use App\Application\Caching\StopCache;
use App\Application\DTOs\Inputs\SearchStopsInput;
use App\Application\DTOs\StopDTO;
use App\Application\Tasks\Stop\FindStopsBySearchQueryTask;
use App\Domain\Entity\Stop;
use App\Domain\ValueObjects\Coordinates;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class GetStopsActionTest extends AbstractTestCase
{
    private FindStopsBySearchQueryTask|MockObject $findStopsBySearchQueryTask;
    private StopCache|MockObject $cache;
    private GetStopsAction $action;

    protected function setUp(): void
    {
        $this->findStopsBySearchQueryTask = $this->createMock(FindStopsBySearchQueryTask::class);
        $this->cache = $this->createMock(StopCache::class);
        $this->action = new GetStopsAction(
            $this->findStopsBySearchQueryTask,
            $this->cache,
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testRun()
    {
        $input = new SearchStopsInput(
            q: 'test',
        );

        $stopId = 1;
        $stop = Stop::create(
            name: 'test',
            coordinates: Coordinates::create(50.0, 32.0),
            cityCode: 1,
        );
        $this->setEntityId($stop, $stopId);

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturnCallback(function (string $key, callable $callback) use ($input) {
                $this->assertEquals("stops.$input->q", $key);

                return $callback();
            });

        $this->findStopsBySearchQueryTask->expects($this->once())
            ->method('run')
            ->with($input)
            ->willReturn([$stop]);

        $expected = StopDTO::create($stop);
        $result = $this->action->run($input);
        $this->assertEquals([$expected], $result);
    }
}
