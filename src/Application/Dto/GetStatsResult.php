<?php

declare(strict_types=1);

namespace App\Application\Dto;

class GetStatsResult
{
    public function __construct(
        public int $totalEvents,
        public int $uniqueUsers,
        public array $topPages
    ) {
    }
}
