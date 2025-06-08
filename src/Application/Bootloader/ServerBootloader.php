<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use App\Infra\Swoole\Server;
use Spiral\Boot\Bootloader\Bootloader;

final class ServerBootloader extends Bootloader
{
    protected const array SINGLETONS = [
        Server::class => Server::class,
    ];
}
