<?php

declare(strict_types=1);

use App\Exceptions\Exception\PatternNotFoundException;
use App\Exceptions\Exception\RouteNotFoundException;
use App\Exceptions\Exception\StopNotFoundException;
use App\Exceptions\Exception\TripNotFoundException;
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

    $parameters->set('app.import_dir', '%kernel.project_dir%/var/imports');


    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\', '../src/');
};
