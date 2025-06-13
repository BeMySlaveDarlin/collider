<?php

declare(strict_types=1);

namespace App\Application\Command;

use DateTimeImmutable;

class CreateEventCommand
{
    public function __construct(
        public int $userId,
        public string $eventType,
        public DateTimeImmutable $timestamp,
        public array $metadata
    ) {
    }
}
