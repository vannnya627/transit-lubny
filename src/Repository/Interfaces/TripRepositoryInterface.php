<?php

declare(strict_types=1);

namespace App\Repository\Interfaces;

use App\Entity\Trip\Trip;

interface TripRepositoryInterface
{
    /**
     * @return list<Trip>
     */
    public function findTripsWithStopTimes(int $patternId): array;
}
