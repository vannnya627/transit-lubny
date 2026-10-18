<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $framework = [
        'router' => [
            'default_uri' => '%env(DEFAULT_URI)%',
        ],
    ];

    if ('prod' === $container->env()) {
        $framework['router']['strict_requirements'] = null;
    }

    $container->extension('framework', $framework);
};
