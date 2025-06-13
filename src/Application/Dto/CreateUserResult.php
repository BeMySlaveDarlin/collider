<?php

declare(strict_types=1);

namespace App\Application\Dto;

class CreateUserResult
{
    public function __construct(
        public int $id,
        public string $name
    ) {
    }
}
