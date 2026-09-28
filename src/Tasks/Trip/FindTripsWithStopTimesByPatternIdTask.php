<?php

declare(strict_types=1);

namespace App\Tasks\Trip;

use App\Data\Entity\Trip\Trip;
use App\Data\Repository\Interfaces\TripRepositoryInterface;

final readonly class FindTripsWithStopTimesByPatternIdTask
{
    public function __construct(
        private TripRepositoryInterface $repository,
    ) {}

    /**
     * @return list<Trip>
     */
    public function run(int $patternId): array
    {
        return $this->repository->findTripsWithStopTimes($patternId);
    }
}
