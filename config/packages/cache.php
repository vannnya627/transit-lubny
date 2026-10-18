<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('framework', [
        'cache' => [
            'prefix_seed' => 'marshrutka_lubny',
            'default_redis_provider' => '%env(resolve:REDIS_URL)%',
            'pools' => [
                'route.cache' => [
                    'adapter' => 'cache.adapter.redis',
                    'default_lifetime' => 3600,
                ],
                'stop.cache' => [
                    'adapter' => 'cache.adapter.redis',
                    'default_lifetime' => 3600,
                ],
                'trip.cache' => [
                    'adapter' => 'cache.adapter.redis',
                    'default_lifetime' => 3600,
                ],
                'departure.cache' => [
                    'adapter' => 'cache.adapter.redis',
                    'default_lifetime' => 60,
                ],
            ],
        ],
    ]);
};
