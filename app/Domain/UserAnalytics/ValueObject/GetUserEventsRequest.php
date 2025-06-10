<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\ValueObject;

class GetUserEventsRequest
{
    public function __construct(
        public int $userId,
        public int $limit = 1000
    ) {
    }
}
