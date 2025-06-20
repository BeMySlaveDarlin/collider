<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\ValueObject;

class GetStatsResponse
{
    public function __construct(
        public int $totalEvents,
        public int $uniqueUsers,
        public array $topPages
    ) {
    }
}
