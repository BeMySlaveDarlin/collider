<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\ValueObject;

class CreateUserResponse
{
    public function __construct(
        public int $id,
        public string $name
    ) {
    }
}
