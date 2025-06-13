<?php

declare(strict_types=1);

namespace App\Infrastructure\Seeding;

class RandomNameGenerator
{
    public function generate(): string
    {
        return bin2hex(random_bytes(8));
    }
}
