<?php

declare(strict_types=1);

namespace App\Application\Caching;

use App\Application\Caching\Keys\SearchStopsCacheKey;
use App\Application\DTOs\StopDTO;

interface StopCacheInterface
{
    /**
     * @param callable(): StopDTO $callback
     */
    public function getStop(int $stopId, callable $callback): StopDTO;

    /**
     * @param callable(): list<StopDTO> $callback
     *
     * @return list<StopDTO>
     */
    public function getStops(SearchStopsCacheKey $key, callable $callback): array;

    public function invalidate(): void;
}
