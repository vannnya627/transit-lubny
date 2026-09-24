<?php

declare(strict_types=1);

namespace App\Repository\Interfaces;

use App\Entity\Stop;

interface StopRepositoryInterface
{
    /**
     * @param array<int> $stopsIds
     *
     * @return list<Stop>
     */
    public function findStopsByIds(array $stopsIds): array;
}
