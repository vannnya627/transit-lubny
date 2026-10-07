<?php

declare(strict_types=1);

namespace App\Actions\Stop;

use App\Caching\Interfaces\CacheInterface;
use App\Data\Entity\Stop;
use App\DTOs\Input\SearchStopsInput;
use App\DTOs\StopDTO;
use App\Tasks\Stop\FindStopsBySearchQueryTask;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class GetStopsAction
{
    public function __construct(
        private FindStopsBySearchQueryTask $findStopsBySearchQueryTask,
        private CacheInterface $cache,
        #[Autowire(param: 'app.cache_ttl')]
        private int $ttlMinutes,
    ) {}

    /**
     * @return list<StopDTO>
     */
    public function run(SearchStopsInput $input): array
    {

        return $this->cache->get(
            key: 'stops',
            ttlMinutes: $this->ttlMinutes,
            callback: function () use ($input) {
                $stops = $this->findStopsBySearchQueryTask->run($input);

                return array_map(fn(Stop $stop) => new StopDTO(
                    id: $stop->id,
                    name: $stop->name,
                    coordinates: $stop->coordinates,
                    cityCode: $stop->cityCode,
                ), $stops);
            },
        );
    }
}
