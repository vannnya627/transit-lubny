<?php

declare(strict_types=1);

namespace App\Tests\Tasks\Stop;

use App\Data\Entity\Stop;
use App\Data\Repository\Interfaces\StopRepositoryInterface;
use App\Data\ValueObjects\Coordinates;
use App\DTOs\Input\SearchStopsInput;
use App\Tasks\Stop\FindStopsBySearchQueryTask;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class FindStopsBySearchQueryTaskTest extends AbstractTestCase
{
    private StopRepositoryInterface|MockObject $repository;
    private FindStopsBySearchQueryTask $task;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(StopRepositoryInterface::class);
        $this->task = new FindStopsBySearchQueryTask($this->repository);
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

        $this->repository->expects($this->once())
            ->method('findStopsBySearchQuery')
            ->with($input)
            ->willReturn([$stop]);

        $result = $this->task->run($input);
        $this->assertEquals([$stop], $result);

    }
}
