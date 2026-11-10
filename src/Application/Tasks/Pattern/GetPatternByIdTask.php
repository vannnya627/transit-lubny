<?php

declare(strict_types=1);

namespace App\Application\Tasks\Pattern;

use App\Domain\Entity\Pattern\Pattern;
use App\Domain\Repositories\PatternReadRepositoryInterface;

readonly class GetPatternByIdTask
{
    public function __construct(
        private PatternReadRepositoryInterface $repository,
    ) {}

    public function run(int $patternId): Pattern
    {
        return $this->repository->getPatternById($patternId);
    }
}
