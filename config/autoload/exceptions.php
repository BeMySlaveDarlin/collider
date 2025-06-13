<?php

declare(strict_types=1);

use App\Infrastructure\Exception\Handler\AppExceptionHandler;

return [
    'handler' => [
        'http' => [
            AppExceptionHandler::class,
        ],
    ],
];
