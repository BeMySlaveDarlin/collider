<?php

declare(strict_types=1);

use App\Infrastructure\Endpoint\Web\EventController;
use App\Infrastructure\Endpoint\Web\IndexController;
use App\Infrastructure\Endpoint\Web\StatsController;
use App\Infrastructure\Endpoint\Web\UserController;
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', [IndexController::class, 'index']);
Router::addRoute(['GET', 'POST', 'HEAD'], '/cache_clear', [IndexController::class, 'clearCache']);

Router::addRoute(['POST'], '/users', [UserController::class, 'create']);
Router::addRoute(['GET'], '/users/{userId}/events', [UserController::class, 'events']);

Router::addRoute(['GET'], '/stats', [StatsController::class, 'index']);

Router::addRoute(['POST'], '/events', [EventController::class, 'create']);
Router::addRoute(['POST'], '/events/batch', [EventController::class, 'createBatch']);
Router::addRoute(['GET'], '/events', [EventController::class, 'events']);
Router::addRoute(['GET'], '/events/total', [EventController::class, 'total']);
Router::addRoute(['DELETE'], '/events', [EventController::class, 'delete']);

Router::get('/favicon.ico', static fn() => '');
