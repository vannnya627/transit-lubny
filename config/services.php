<?php

declare(strict_types=1);

use App\Domain\Exceptions\PatternNotFoundException;
use App\Domain\Exceptions\RouteNotFoundException;
use App\Domain\Exceptions\StopNotFoundException;
use App\Domain\Exceptions\TripNotFoundException;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {

    $parameters = $container->parameters();

    $parameters
        ->set('exceptions', [
            RouteNotFoundException::class => [
                'type' => 'route-not-found',
                'code' => 404,
                'loggable' => false,
            ],
            PatternNotFoundException::class => [
                'type' => 'pattern-not-found',
                'code' => 404,
                'loggable' => false,
            ],
            StopNotFoundException::class => [
                'type' => 'stop-not-found',
                'code' => 404,
                'loggable' => false,
            ],
            TripNotFoundException::class => [
                'type' => 'trip-not-found',
                'code' => 404,
                'loggable' => false,
            ],
        ]);

    $importDir = '%kernel.project_dir%/var/imports';
    if ('test' === $container->env()) {
        $importDir = '%kernel.project_dir%/var/test_imports';
    }

    $parameters->set('app.import_dir', $importDir);


    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\', '../src/');
};
