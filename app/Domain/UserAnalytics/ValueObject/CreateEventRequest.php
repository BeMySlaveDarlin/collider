<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\ValueObject;

use DateTimeImmutable;

class CreateEventRequest
{
    public function __construct(
        public int $userId,
        public string $eventType,
        public DateTimeImmutable $timestamp,
        public array $metadata
    ) {
    }
}
