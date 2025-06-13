<?php

declare(strict_types=1);

use Hyperf\Cache\Driver\MemoryDriver;
use Hyperf\Codec\Packer\IgbinarySerializerPacker;

return [
    'default' => [
        'driver' => MemoryDriver::class,
        'packer' => IgbinarySerializerPacker::class,
        'prefix' => 'c:',
        'skip_cache_results' => [],
    ],
];
