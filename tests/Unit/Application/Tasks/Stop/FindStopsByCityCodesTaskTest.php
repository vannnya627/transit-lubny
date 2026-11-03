<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Tasks\Stop;

use App\Application\Tasks\Stop\FindStopsByCityCodesTask;
use App\Domain\Entity\Stop;
use App\Domain\Repositories\StopRepositoryInterface;
use App\Domain\ValueObjects\Coordinates;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class FindStopsByCityCodesTaskTest extends AbstractTestCase
{
    private StopRepositoryInterface|MockObject $repository;
    private FindStopsByCityCodesTask $task;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(StopRepositoryInterface::class);
        $this->task = new FindStopsByCityCodesTask($this->repository);
    }

    /**
     * @throws ReflectionException
     */
    public function testRun()
    {
        $cityCode = 1;
        $stopId = 1;
        $stop = Stop::create(
            name: 'test',
            coordinates: Coordinates::create(50.0, 32.0),
            cityCode: $cityCode,
        );
        $this->setEntityId($stop, $stopId);

        $this->repository->expects($this->once())
            ->method('findStopsByCityCodes')
            ->with([$cityCode])
            ->willReturn([$stop]);

        $result = $this->task->run([$cityCode]);
        $this->assertEquals([$stop], $result);

    }
}
