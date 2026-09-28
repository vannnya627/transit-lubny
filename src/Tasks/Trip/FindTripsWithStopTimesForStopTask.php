<?php

declare(strict_types=1);

namespace App\Tasks\Trip;

use App\Data\Entity\Trip\Trip;
use App\Data\Repository\Interfaces\TripRepositoryInterface;
use App\DTOs\Input\GetStopDeparturesInput;

final readonly class FindTripsWithStopTimesForStopTask
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
