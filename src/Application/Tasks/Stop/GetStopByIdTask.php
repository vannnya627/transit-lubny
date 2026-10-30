<?php

declare(strict_types=1);

namespace App\Application\Tasks\Stop;

use App\Domain\Entity\Stop;
use App\Domain\Repositories\StopRepositoryInterface;

readonly class GetStopByIdTask
{
    public function __construct(
        private StopRepositoryInterface $repository,
    ) {}

    public function run(int $stopId): Stop
    {
        return $this->repository->getStopById($stopId);
    }
}
