<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\ValueObject;

final readonly class CreateUserRequest
{
    public function __construct(
        public string $name
    ) {
    }
}
