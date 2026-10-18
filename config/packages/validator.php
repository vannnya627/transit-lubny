<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $framework = [
        'validation' => [],
    ];

    if ('test' === $container->env()) {
        $framework['validation']['not_compromised_password'] = false;
    }

    $container->extension('framework', $framework);
};
