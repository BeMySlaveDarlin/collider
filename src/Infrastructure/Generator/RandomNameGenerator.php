<?php

declare(strict_types=1);

namespace App\Infrastructure\Generator;

class RandomNameGenerator
{
    public function generate(): string
    {
        return bin2hex(random_bytes(8));
    }
}
