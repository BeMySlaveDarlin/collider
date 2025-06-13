<?php

declare(strict_types=1);

use App\Infrastructure\Endpoint\Web\EventEndpoint;
use App\Infrastructure\Endpoint\Web\IndexEndpoint;
use App\Infrastructure\Endpoint\Web\StatsEndpoint;
use App\Infrastructure\Endpoint\Web\UserEndpoint;
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', [IndexEndpoint::class, 'index']);
Router::addRoute(['GET', 'POST', 'HEAD'], '/cache_clear', [IndexEndpoint::class, 'clearCache']);

Router::addRoute(['POST'], '/users', [UserEndpoint::class, 'create']);
Router::addRoute(['GET'], '/users/{userId}/events', [UserEndpoint::class, 'events']);

Router::addRoute(['GET'], '/stats', [StatsEndpoint::class, 'index']);

Router::addRoute(['POST'], '/events', [EventEndpoint::class, 'create']);
Router::addRoute(['POST'], '/events/batch', [EventEndpoint::class, 'createBatch']);
Router::addRoute(['GET'], '/events', [EventEndpoint::class, 'events']);
Router::addRoute(['GET'], '/events/total', [EventEndpoint::class, 'total']);
Router::addRoute(['DELETE'], '/events', [EventEndpoint::class, 'delete']);

Router::get('/favicon.ico', static fn() => '');
