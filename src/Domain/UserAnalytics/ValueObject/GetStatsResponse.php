<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\ValueObject;

final readonly class GetStatsResponse
{
    public function __construct(
        public int $totalEvents,
        public int $uniqueUsers,
        public array $topPages
    ) {
    }
}
