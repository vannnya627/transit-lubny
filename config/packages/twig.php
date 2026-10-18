<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $twig = [
        'file_name_pattern' => '*.twig',
    ];

    if ('test' === $container->env()) {
        $twig['strict_variables'] = true;
    }

    $container->extension('twig', $twig);
};
