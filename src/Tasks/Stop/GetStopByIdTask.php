<?php

declare(strict_types=1);

namespace App\Tasks\Stop;

use App\Data\Entity\Stop;
use App\Data\Repository\Interfaces\StopRepositoryInterface;

final readonly class GetStopByIdTask
{
    public function __construct(
        private StopRepositoryInterface $repository,
    ) {}

    public function run(int $stopId): Stop
    {
        return $this->repository->getStopById($stopId);
    }
}
