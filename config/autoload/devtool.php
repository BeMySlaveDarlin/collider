<?php

declare(strict_types=1);

return [
    'generator' => [
        'command' => [
            'namespace' => 'App\\Endpoint\\Command',
        ],
        'controller' => [
            'namespace' => 'App\\Endpoint\\Web',
        ],
        'aspect' => [
            'namespace' => 'App\\Application\\Aspect',
        ],
        'job' => [
            'namespace' => 'App\\Application\\Job',
        ],
        'listener' => [
            'namespace' => 'App\\Application\\Listener',
        ],
        'middleware' => [
            'namespace' => 'App\\Application\\Middleware',
        ],
        'Process' => [
            'namespace' => 'App\\Application\\Process',
        ],
    ],
];
