<?php

declare(strict_types=1);

namespace App\Application\Tasks\Trip;

use App\Domain\Entity\Trip\Trip;
use App\Domain\Repositories\TripRepositoryInterface;

readonly class FindTripsByPatternIdsTask
{
    public function __construct(
        private TripRepositoryInterface $repository,
    ) {}

    /**
     * @param array<int, int> $patternIds
     *
     * @return list<Trip>
     */
    public function run(array $patternIds): array
    {
        return $this->repository->findTrips($patternIds);
    }
}
