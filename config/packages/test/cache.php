<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('framework', [
        'cache' => [
            'prefix_seed' => 'marshrutka_lubny',
            'pools' => [
                'route.cache' => [
                    'adapter' => 'cache.adapter.array',
                ],
                'stop.cache' => [
                    'adapter' => 'cache.adapter.array',
                ],
                'trip.cache' => [
                    'adapter' => 'cache.adapter.array',
                ],
                'departure.cache' => [
                    'adapter' => 'cache.adapter.array',
                ],
            ],
        ],
    ]);
};
