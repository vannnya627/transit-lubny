<?php

declare(strict_types=1);

namespace App\Tasks\Route;

use App\Entity\Route\Pattern;
use App\Repository\Interfaces\RouteRepositoryInterface;

final readonly class GetPatternByIdTask
{
    public function __construct(
        private RouteRepositoryInterface $repository,
    ) {}

    public function run(int $patternId): Pattern
    {
        return $this->repository->getPatternById($patternId);
    }
}
