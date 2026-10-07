<?php

declare(strict_types=1);

namespace App\Actions\Route;

use App\Caching\Interfaces\CacheInterface;
use App\Data\Entity\Route\Route;
use App\DTOs\RouteDTO;
use App\Tasks\Route\GetAllRouteTask;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class GetAllRouteAction
{
    public function __construct(
        private GetAllRouteTask $getAllRouteTask,
        private CacheInterface $cache,
        #[Autowire(param: 'app.cache_ttl')]
        private int $ttlMinutes,
    ) {}

    /**
     * @return list<RouteDTO>
     */
    public function run(): array
    {
        return $this->cache->get(
            key: 'routes',
            ttlMinutes: $this->ttlMinutes,
            callback: function (): array {
                $routes = $this->getAllRouteTask->run();

                return array_map(
                    fn(Route $route) => new RouteDTO(
                        id: $route->id,
                        shortName: $route->shortName,
                        longName: $route->longName,
                    ),
                    $routes,
                );
            },
        );

    }
}
