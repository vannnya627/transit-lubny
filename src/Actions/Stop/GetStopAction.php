<?php

declare(strict_types=1);

namespace App\Actions\Stop;

use App\Caching\Interfaces\CacheInterface;
use App\DTOs\StopDTO;
use App\Tasks\Stop\GetStopByIdTask;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class GetStopAction
{
    public function __construct(
        private GetStopByIdTask $getStopByIdTask,
        private CacheInterface $cache,
        #[Autowire(param: 'app.cache_ttl')]
        private int $ttlMinutes,
    ) {}

    public function run(int $stopId): StopDTO
    {
        return $this->cache->get(
            key: "stop.$stopId",
            ttlMinutes: $this->ttlMinutes,
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
