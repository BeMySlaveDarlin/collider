<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\ValueObject;

final readonly class GetUserEventsResponse
{
    public function __construct(
        public array $events
    ) {
    }
}
