<?php

declare(strict_types=1);

namespace App\Application\Tasks\Route;

use App\Domain\Entity\Route\Pattern;
use App\Domain\Repositories\RouteRepositoryInterface;

readonly class GetPatternByIdTask
{
    public function __construct(
        private RouteRepositoryInterface $repository,
    ) {}

    public function run(int $patternId): Pattern
    {
        return $this->repository->getPatternById($patternId);
    }
}
