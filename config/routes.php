<?php

declare(strict_types=1);

use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Endpoint\Web\IndexController@index');
Router::addRoute(['GET', 'POST', 'HEAD'], '/cache_clear', 'App\Endpoint\Web\IndexController@clearCache');

Router::addRoute(['POST'], '/users', 'App\Endpoint\Web\UserController@create');
Router::addRoute(['GET'], '/users/events', 'App\Endpoint\Web\UserController@events');

Router::addRoute(['GET'], '/stats', 'App\Endpoint\Web\StatsController@index');

Router::addRoute(['POST'], '/event', 'App\Endpoint\Web\EventController@create');
Router::addRoute(['POST'], '/events', 'App\Endpoint\Web\EventController@createBatch');
Router::addRoute(['GET'], '/events', 'App\Endpoint\Web\EventController@events');
Router::addRoute(['GET'], '/events/total', 'App\Endpoint\Web\EventController@total');
Router::addRoute(['DELETE'], '/events', 'App\Endpoint\Web\EventController@delete');

Router::get('/favicon.ico', static function () {
    return '';
});
