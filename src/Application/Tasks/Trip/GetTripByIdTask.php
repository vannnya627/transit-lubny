<?php

declare(strict_types=1);

namespace App\Application\Tasks\Trip;

use App\Domain\Entity\Trip\Trip;
use App\Domain\Repositories\TripRepositoryInterface;

readonly class GetTripByIdTask
{
    public function __construct(
        private TripRepositoryInterface $repository,
    ) {}

    public function run(int $tripId): Trip
    {
        return $this->repository->getTripById($tripId);
    }
}
