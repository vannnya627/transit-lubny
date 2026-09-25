<?php

declare(strict_types=1);

namespace App\Repository\Interfaces;

use App\DTOs\Input\SearchStopsInput;
use App\Entity\Stop;

interface StopRepositoryInterface
{
    /**
     * @param array<int> $stopsIds
     *
     * @return list<Stop>
     */
    public function findStopsByIds(array $stopsIds): array;

    /**
     * @return list<Stop>
     */
    public function findStopsBySearchQuery(SearchStopsInput $input): array;

    public function getStopById(int $stopId): Stop;
}
