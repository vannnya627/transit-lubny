<?php

declare(strict_types=1);

namespace App\Repository\Interfaces;

use App\DTOs\Input\GetStopDeparturesInput;
use App\Entity\Trip\Trip;

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
}
