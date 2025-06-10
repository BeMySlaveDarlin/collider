<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\ValueObject;

class GetUserEventsResponse
{
    public function __construct(
        public array $events
    ) {
    }
}
