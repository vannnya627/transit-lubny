<?php

declare(strict_types=1);

namespace App\Application\Caching;

use App\Application\DTOs\TripDetailsDTO;

interface TripCacheInterface
{
    /**
     * @param callable(): TripDetailsDTO $callback
     */
    public function getTrip(int $tripId, callable $callback): TripDetailsDTO;

    public function invalidate(): void;
}
