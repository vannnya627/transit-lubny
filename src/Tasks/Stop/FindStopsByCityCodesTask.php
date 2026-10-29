<?php

declare(strict_types=1);

namespace App\Tasks\Stop;

use App\Data\Entity\Stop;
use App\Data\Repository\Interfaces\StopRepositoryInterface;

readonly class FindStopsByCityCodesTask
{
    public function __construct(
        private StopRepositoryInterface $repository,
    ) {}

    /**
     * @param array<int, int> $cityCodes
     *
     * @return list<Stop>
     */
    public function run(array $cityCodes): array
    {
        return $this->repository->findStopsByCityCodes($cityCodes);
    }
}
