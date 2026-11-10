<?php

declare(strict_types=1);

namespace App\Application\Tasks\Pattern;

use App\Domain\Entity\Route\Pattern\Pattern;
use App\Domain\Repositories\Route\Pattern\PatternReadRepositoryInterface;

readonly class GetPatternWithRouteByPatternIdTask
{
    public function __construct(
        public PatternReadRepositoryInterface $patternRepository,
    ) {}

    public function run(int $patternId): Pattern
    {
        return  $this->patternRepository->getPatternWithRouteById($patternId);
    }
}
