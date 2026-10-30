<?php

declare(strict_types=1);

namespace App\Application\Tasks\Stop;

use App\Domain\Entity\Stop;
use App\Domain\Repositories\StopRepositoryInterface;

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
