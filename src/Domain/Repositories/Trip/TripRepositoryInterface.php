<?php

declare(strict_types=1);

namespace App\Domain\Repositories\Trip;

use App\Domain\Entity\Trip\Trip;

interface TripRepositoryInterface
{
    /**
     * @return list<Trip>
     */
    public function findTripsWithStopTimes(int $patternId): array;

    public function getTripById(int $tripId): Trip;

    public function remove(Trip $trip): void;

    public function commit(): void;

    public function save(Trip $trip): void;

    /**
     * @param array<int,int> $patternIds
     *
     * @return list<Trip>
     */
    public function findTrips(array $patternIds): array;
}
