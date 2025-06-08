<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\ValueObject;

final readonly class GetEventsRequest
{
    public function __construct(
        public int $page = 1,
        public int $limit = 100
    ) {
    }
}
