<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Bootloader\Http\RoutesBootloader as BaseRoutesBootloader;
use Spiral\Debug\StateCollector\HttpCollector;
use Spiral\Http\Middleware\ErrorHandlerMiddleware;
use Spiral\Http\Middleware\JsonPayloadMiddleware;
use Spiral\Router\Bootloader\AnnotatedRoutesBootloader;
use Spiral\Router\Loader\Configurator\RoutingConfigurator;

final class RoutesBootloader extends BaseRoutesBootloader
{
    protected const array DEPENDENCIES = [AnnotatedRoutesBootloader::class];

    #[Override]
    protected function globalMiddleware(): array
    {
        return [
            ErrorHandlerMiddleware::class,
            JsonPayloadMiddleware::class,
            HttpCollector::class,
        ];
    }

    #[Override]
    protected function middlewareGroups(): array
    {
        return [];
    }

    #[Override]
    protected function defineRoutes(RoutingConfigurator $routes): void
    {
        $routes
            ->default('/<path:.*>')
            ->callable(function (ServerRequestInterface $r, ResponseInterface $response) {
                return $response->withStatus(404, 'Not Found');
            });
    }
}
