<?php

declare(strict_types=1);

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use DoctrineExtensions\Query\Mysql\Acos;
use DoctrineExtensions\Query\Mysql\Cos;
use DoctrineExtensions\Query\Mysql\Radians;
use DoctrineExtensions\Query\Mysql\Sin;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {

    $dbal = [
        'url' => '%env(resolve:DATABASE_URL)%',
        'profiling_collect_backtrace' => '%kernel.debug%',
    ];

    if ('test' === $container->env()) {
        $dbal['dbname_suffix'] = '_test%env(default::TEST_TOKEN)%';
    }

    $orm = [
        'validate_xml_mapping' => true,
        'naming_strategy' => 'doctrine.orm.naming_strategy.underscore',
        'identity_generation_preferences' => [
            PostgreSQLPlatform::class => 'identity',
        ],
        'auto_mapping' => true,
        'mappings' => [
            'App' => [
                'type' => 'attribute',
                'is_bundle' => false,
                'dir' => '%kernel.project_dir%/src/Data/Entity',
                'prefix' => 'App\Data\Entity',
                'alias' => 'App',
            ],
            'ValueObject' => [
                'type' => 'attribute',
                'is_bundle' => false,
                'dir' => '%kernel.project_dir%/src/Data/ValueObjects',
                'prefix' => 'App\Data\ValueObjects',
            ],
        ],
        'dql' => [
            'numeric_functions' => [
                'ACOS' => Acos::class,
                'COS' => Cos::class,
                'RADIANS' => Radians::class,
                'SIN' => Sin::class,
            ],
        ],
    ];

    if ('prod' === $container->env()) {
        $orm['query_cache_driver'] = [
            'type' => 'pool',
            'pool' => 'doctrine.system_cache_pool',
        ];
        $orm['result_cache_driver'] = [
            'type' => 'pool',
            'pool' => 'doctrine.result_cache_pool',
        ];

        $container->extension('framework', [
            'cache' => [
                'pools' => [
                    'doctrine.result_cache_pool' => [
                        'adapter' => 'cache.app',
                    ],
                    'doctrine.system_cache_pool' => [
                        'adapter' => 'cache.system',
                    ],
                ],
            ],
        ]);
    }

    $container->extension('doctrine', [
        'dbal' => $dbal,
        'orm' => $orm,
    ]);
};
