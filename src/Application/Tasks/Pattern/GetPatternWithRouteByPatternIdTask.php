<?php

declare(strict_types=1);

namespace App\Application\Tasks\Pattern;

use App\Domain\Entity\Pattern\Pattern;
use App\Domain\Repositories\PatternRepositoryInterface;

readonly class GetPatternWithRouteByPatternIdTask
{
    public function __construct(
        public PatternRepositoryInterface $patternRepository,
    ) {}

    public function run(int $patternId): Pattern
    {
        return  $this->patternRepository->getPatternWithRouteById($patternId);
    }
}
