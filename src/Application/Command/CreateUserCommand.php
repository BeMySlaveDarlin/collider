<?php

declare(strict_types=1);

namespace App\Application\Command;

class CreateUserCommand
{
    public function __construct(
        public string $name
    ) {
    }
}
