<?php

declare(strict_types=1);

namespace App\Tests\Actions\Stop;

use App\Actions\Stop\GetStopsAction;
use App\Data\Entity\Stop;
use App\Data\ValueObjects\Coordinates;
use App\DTOs\Input\SearchStopsInput;
use App\DTOs\StopDTO;
use App\Tasks\Stop\FindStopsBySearchQueryTask;
use App\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class GetStopsActionTest extends AbstractTestCase
{
    private FindStopsBySearchQueryTask|MockObject $findStopsBySearchQueryTask;
    private GetStopsAction $action;

    protected function setUp(): void
    {
        $this->findStopsBySearchQueryTask = $this->createMock(FindStopsBySearchQueryTask::class);
        $this->action = new GetStopsAction(
            $this->findStopsBySearchQueryTask,
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

        $this->findStopsBySearchQueryTask->expects($this->once())
            ->method('run')
            ->with($input)
            ->willReturn([$stop]);

        $expected = new StopDTO(
            id: $stop->id,
            name: $stop->name,
            coordinates: $stop->coordinates,
            cityCode: $stop->cityCode,
        );
        $result = $this->action->run($input);
        $this->assertEquals([$expected], $result);
    }
}
