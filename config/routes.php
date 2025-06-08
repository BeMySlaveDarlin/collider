<?php

declare(strict_types=1);

return [
    'cache' => [
        'enabled' => true,
        'directory' => directory('runtime') . '/cache/routes',
    ],
    'directories' => [
        directory('app') . '/Endpoint/Web',
    ],
];
