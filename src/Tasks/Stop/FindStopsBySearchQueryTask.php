<?php

declare(strict_types=1);

namespace App\Tasks\Stop;

use App\DTOs\Input\SearchStopsInput;
use App\Entity\Stop;
use App\Repository\Interfaces\StopRepositoryInterface;

final readonly class FindStopsBySearchQueryTask
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
