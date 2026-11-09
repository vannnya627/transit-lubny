<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entity\Trip\StopTime;
use App\Domain\ValueObjects\TimeOfDay;

interface StopTimeReadRepositoryInterface
{
    /**
     * @return list<StopTime>
     */
    public function findStopTimesWithTrip(int $stopId, TimeOfDay $time, ?int $limit = null): array;
}
