<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\ValueObject;

use DateTimeImmutable;

class DeleteEventsRequest
{
    public function __construct(
        public DateTimeImmutable $before
    ) {
    }
}
