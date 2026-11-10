<?php

declare(strict_types=1);

namespace App\Application\Tasks\StopTime;

use App\Application\DTOs\Inputs\GetStopDeparturesInput;
use App\Domain\Entity\Trip\StopTime;
use App\Domain\Repositories\Trip\StopTimeReadRepositoryInterface;

readonly class FindStopTimesWithTripTask
{
    public function __construct(
        private StopTimeReadRepositoryInterface $repository,
    ) {}

    /**
     * @return list<StopTime>
     */
    public function run(GetStopDeparturesInput $input): array
    {
        return $this->repository->findStopTimesWithTrip(
            stopId: $input->stopId,
            time: $input->time,
            limit: $input->limit,
        );
    }
}
