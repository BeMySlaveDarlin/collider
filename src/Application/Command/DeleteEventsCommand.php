<?php

declare(strict_types=1);

namespace App\Application\Command;

use DateTimeImmutable;

class DeleteEventsCommand
{
    public function __construct(
        public DateTimeImmutable $before
    ) {
    }
}
