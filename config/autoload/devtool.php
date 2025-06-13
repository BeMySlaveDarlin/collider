<?php

declare(strict_types=1);

return [
    'generator' => [
        'command' => [
            'namespace' => 'App\\Infrastructure\\Endpoint\\Command',
        ],
        'controller' => [
            'namespace' => 'App\\Infrastructure\\Endpoint\\Web',
        ],
        'aspect' => [
            'namespace' => 'App\\Infrastructure\\Aspect',
        ],
        'job' => [
            'namespace' => 'App\\Infrastructure\\Job',
        ],
        'listener' => [
            'namespace' => 'App\\Infrastructure\\Listener',
        ],
        'middleware' => [
            'namespace' => 'App\\Infrastructure\\Middleware',
        ],
        'Process' => [
            'namespace' => 'App\\Infrastructure\\Process',
        ],
    ],
];
