<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('app.swagger', '/api/doc.json')
        ->methods(['GET'])
        ->controller('nelmio_api_doc.controller.swagger');

    $routes->add('app.swagger_ui', '/api/doc')
        ->methods(['GET'])
        ->controller('nelmio_api_doc.controller.swagger_ui');
};
