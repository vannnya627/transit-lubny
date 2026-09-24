<?php

declare(strict_types=1);

namespace App\Tasks\Stop;

use App\Entity\Stop;
use App\Repository\Interfaces\StopRepositoryInterface;

final readonly class FindStopsByIdsTask
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
        return $this->repository->findStopsByIds($stopsIds);
    }
}
