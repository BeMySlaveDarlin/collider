<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use App\Endpoint\Console\EventsSeedCommand;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Console\Bootloader\ConsoleBootloader as SpiralConsoleBootloader;

final class ConsoleBootloader extends Bootloader
{
    protected const array DEPENDENCIES = [
        SpiralConsoleBootloader::class,
    ];

    public function boot(SpiralConsoleBootloader $console): void
    {
        $console->addCommand(EventsSeedCommand::class);
    }
}
