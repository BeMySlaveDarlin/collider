<?php

declare(strict_types=1);

return [
    'name' => 'Swoole Analytics App',
    'version' => '1.0.0',
    'timezone' => 'UTC',
    'app_env' => $_ENV['APP_ENV'] ?? 'dev',
    'debug' => $_ENV['DEBUG'] ?? false,
];
