<?php

declare(strict_types=1);

namespace App\Tasks\Stop;

use App\Data\Entity\Stop;
use App\Data\Repository\Interfaces\StopRepositoryInterface;
use App\DTOs\Input\SearchStopsInput;

readonly class FindStopsBySearchQueryTask
{
    public function __construct(
        private StopRepositoryInterface $repository,
    ) {}

    /**
     * @return list<Stop>
     */
    public function run(SearchStopsInput $input): array
    {
        return $this->repository->findStopsBySearchQuery($input);
    }
}
