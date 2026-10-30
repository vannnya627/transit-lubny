<?php

declare(strict_types=1);

namespace App\Application\Tasks\Stop;

use App\Application\DTOs\Inputs\SearchStopsInput;
use App\Domain\Entity\Stop;
use App\Domain\Repositories\StopRepositoryInterface;

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
