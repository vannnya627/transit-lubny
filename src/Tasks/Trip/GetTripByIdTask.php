<?php

declare(strict_types=1);

namespace App\Tasks\Trip;

use App\Data\Entity\Trip\Trip;
use App\Data\Repository\Interfaces\TripRepositoryInterface;

final readonly class GetTripByIdTask
{
    public function __construct(
        private TripRepositoryInterface $repository,
    ) {}

    public function run(int $tripId): Trip
    {
        return $this->repository->getTripById($tripId);
    }
}
