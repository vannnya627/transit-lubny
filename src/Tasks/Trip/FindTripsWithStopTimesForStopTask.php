<?php

declare(strict_types=1);

namespace App\Tasks\Trip;

use App\DTOs\Input\GetStopDeparturesInput;
use App\Entity\Trip\Trip;
use App\Repository\Interfaces\TripRepositoryInterface;

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
