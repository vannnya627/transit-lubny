<?php

declare(strict_types=1);

namespace App\Tasks\Route;

use App\Data\Entity\Route\Pattern;
use App\Data\Repository\Interfaces\RouteRepositoryInterface;

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
