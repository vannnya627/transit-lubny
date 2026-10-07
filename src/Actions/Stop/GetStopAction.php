<?php

declare(strict_types=1);

namespace App\Actions\Stop;

use App\Caching\StopCache;
use App\DTOs\StopDTO;
use App\Tasks\Stop\GetStopByIdTask;

final readonly class GetStopAction
{
    public function __construct(
        private GetStopByIdTask $getStopByIdTask,
        private StopCache $cache,
    ) {}

    public function run(int $stopId): StopDTO
    {
        return $this->cache->get(
            key: "stop.$stopId",
            callback: function () use ($stopId) {
                $stop = $this->getStopByIdTask->run($stopId);

                return new StopDTO(
                    id: $stop->id,
                    name: $stop->name,
                    coordinates: $stop->coordinates,
                    cityCode: $stop->cityCode,
                );
            },
        );

    }
}
