<?php

declare(strict_types=1);

namespace App\Tests\Tasks\Stop;

use App\Data\Entity\Stop;
use App\Data\Repository\Interfaces\StopRepositoryInterface;
use App\Data\ValueObjects\Coordinates;
use App\Tasks\Stop\GetStopByIdTask;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class GetStopByIdTaskTest extends AbstractTestCase
{
    private StopRepositoryInterface|MockObject $repository;
    private GetStopByIdTask $task;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(StopRepositoryInterface::class);
        $this->task = new GetStopByIdTask($this->repository);
    }

    /**
     * @throws ReflectionException
     */
    public function testRun()
    {
        $stopId = 1;
        $stop = Stop::create(
            name: 'test',
            coordinates: Coordinates::create(50.0, 32.0),
            cityCode: 1,
        );
        $this->setEntityId($stop, $stopId);

        $this->repository->expects($this->once())
            ->method('getStopById')
            ->with($stopId)
            ->willReturn($stop);

        $result = $this->task->run($stopId);
        $this->assertEquals($stop, $result);

    }
}
