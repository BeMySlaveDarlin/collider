<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\ValueObject;

use DateTimeImmutable;

final readonly class DeleteEventsRequest
{
    public function __construct(
        public DateTimeImmutable $before
    ) {
    }
}
