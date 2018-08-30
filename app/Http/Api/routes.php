<?php

use CatLab\Charon\Collections\RouteCollection;

$routes = new RouteCollection();

$routes
    ->get('/api/v1/description.{format?}', 'Controllers\DescriptionController@description')
    ->summary('Get swagger API description')
    ->returns()->statusCode(200)->describe('JSON Swagger Response')
    ->tag('swagger')
;

/**
 * Everything related to the API.
 */
$routes->group(
    [
        'prefix' => '/api/v1/',
        'namespace' => 'Controllers',
        'middleware' => [ 'jwt' ]
    ],
    function(RouteCollection $routes)
    {
        \App\Http\Api\Controllers\ProjectController::setRoutes($routes);
        \App\Http\Api\Controllers\FieldController::setRoutes($routes);
        \App\Http\Api\Controllers\LinkController::setRoutes($routes);
        \App\Http\Api\Controllers\CalculatedFieldController::setRoutes($routes);
        \App\Http\Api\Controllers\Teamwork\TeamworkController::setRoutes($routes);
        \App\Http\Api\Controllers\Teamwork\ProjectController::setRoutes($routes);
    }
);

return $routes;