<?php

declare(strict_types=1);

namespace App\Data\Repository\Interfaces;

use App\Data\Entity\Trip\Trip;
use App\DTOs\Input\GetStopDeparturesInput;

interface TripRepositoryInterface
{
    /**
     * @return list<Trip>
     */
    public function findTripsWithStopTimes(int $patternId): array;

    /**
     * @return list<Trip>
     */
    public function findTripsWithStopTimesForStop(GetStopDeparturesInput $input): array;

    public function getTripById(int $tripId): Trip;
}
