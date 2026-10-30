<?php

declare(strict_types=1);

namespace App\Application\Tasks\Trip;

use App\Application\DTOs\Inputs\GetStopDeparturesInput;
use App\Domain\Entity\Trip\Trip;
use App\Domain\Repositories\TripRepositoryInterface;

readonly class FindTripsWithStopTimesForStopTask
{
    public function __construct(
        private TripRepositoryInterface $repository,
    ) {}

    /**
     * @return list<Trip>
     */
    public function run(GetStopDeparturesInput $input): array
    {
        return $this->repository->findTripsWithStopTimesForStop($input);
    }
}
