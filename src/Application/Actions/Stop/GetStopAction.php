<?php

declare(strict_types=1);

namespace App\Application\Actions\Stop;

use App\Application\Caching\StopCache;
use App\Application\DTOs\StopDTO;
use App\Application\Tasks\Stop\GetStopByIdTask;

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

                return StopDTO::create($stop);
            },
        );

    }
}
