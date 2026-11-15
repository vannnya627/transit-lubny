<?php

declare(strict_types=1);

namespace App\Application\Caching;

use App\Application\Caching\Keys\GetStopDeparturesCacheKey;
use App\Application\DTOs\StopWithDeparturesDTO;

interface DepartureCacheInterface
{
    /**
     * @param callable(): StopWithDeparturesDTO $callback
     */
    public function getStopDepartures(GetStopDeparturesCacheKey $key, callable $callback): StopWithDeparturesDTO;

    public function invalidate(): void;
}
