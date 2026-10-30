<?php

declare(strict_types=1);

namespace App\Application\Tasks\Trip;

use App\Domain\Entity\Trip\Trip;
use App\Domain\Repositories\TripRepositoryInterface;

readonly class FindTripsWithStopTimesByPatternIdTask
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
