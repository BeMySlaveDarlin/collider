<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

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
                directory('logs') . 'http.log',
            ),
        );

        $monolog->addHandler(
            channel: MonologConfig::DEFAULT_CHANNEL,
            handler: $monolog->logRotate(
                filename: directory('logs') . 'error.log',
                level: Level::Error,
                maxFiles: 25,
                bubble: false,
            ),
        );

        $monolog->addHandler(
            channel: MonologConfig::DEFAULT_CHANNEL,
            handler: $monolog->logRotate(
                filename: directory('logs') . 'debug.log',
            ),
        );
    }
}
