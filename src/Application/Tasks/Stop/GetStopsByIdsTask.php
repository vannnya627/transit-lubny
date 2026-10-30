<?php

declare(strict_types=1);

namespace App\Application\Tasks\Stop;

use App\Domain\Entity\Stop;
use App\Domain\Repositories\StopRepositoryInterface;

readonly class GetStopsByIdsTask
{
    public function __construct(
        private StopRepositoryInterface $repository,
    ) {}

    /**
     * @param array<int, int> $stopsIds
     *
     * @return list<Stop>
     */
    public function run(array $stopsIds): array
    {
        return $this->repository->getStopsByIds($stopsIds);
    }
}
