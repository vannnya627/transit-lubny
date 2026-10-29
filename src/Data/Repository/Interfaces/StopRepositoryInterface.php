<?php

declare(strict_types=1);

namespace App\Data\Repository\Interfaces;

use App\Data\Entity\Stop;
use App\DTOs\Input\SearchStopsInput;

interface StopRepositoryInterface
{
    /**
     * @param array<int> $stopsIds
     *
     * @return list<Stop>
     */
    public function getStopsByIds(array $stopsIds): array;

    /**
     * @return list<Stop>
     */
    public function findStopsBySearchQuery(SearchStopsInput $input): array;

    public function getStopById(int $stopId): Stop;

    public function commit(): void;

    public function save(Stop $stop): void;

    /**
     * @param array<int, int> $cityCodes
     *
     * @return list<Stop>
     */
    public function findStopsByCityCodes(array $cityCodes): array;
}
