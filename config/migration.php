<?php

declare(strict_types=1);

use Spiral\Boot\Environment\AppEnvironment;

return [
    'directory' => directory('app') . 'database/migrations/',
    'table' => 'migrations',
    'safe' => env('SAFE_MIGRATIONS', spiral(AppEnvironment::class)->isProduction()),
];
