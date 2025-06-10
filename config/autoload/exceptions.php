<?php

declare(strict_types=1);

use App\Application\Exception\Handler\AppExceptionHandler;

return [
    'handler' => [
        'http' => [
            AppExceptionHandler::class,
        ],
    ],
];
