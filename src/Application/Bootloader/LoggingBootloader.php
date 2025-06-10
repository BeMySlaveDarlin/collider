<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use App\Infra\Http\Middleware\HttpRequestMiddleware;
use Monolog\Level;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Http\Middleware\ErrorHandlerMiddleware;
use Spiral\Monolog\Bootloader\MonologBootloader;
use Spiral\Monolog\Config\MonologConfig;

final class LoggingBootloader extends Bootloader
{
    public function init(MonologBootloader $monolog): void
    {
        $monolog->addHandler(
            channel: ErrorHandlerMiddleware::class,
            handler: $monolog->logRotate(
                filename: directory('logs') . 'app.log',
                level: Level::Error,
                maxFiles: 25,
                bubble: false,
            ),
        );
        $monolog->addHandler(
            channel: HttpRequestMiddleware::class,
            handler: $monolog->logRotate(
                filename: directory('logs') . 'app.log',
                maxFiles: 25,
                bubble: false,
            ),
        );
        $monolog->addHandler(
            channel: MonologConfig::DEFAULT_CHANNEL,
            handler: $monolog->logRotate(
                filename: directory('logs') . 'app.log',
                maxFiles: 25,
                bubble: false,
            ),
        );
    }
}
