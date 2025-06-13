<?php

declare(strict_types=1);

namespace App\Application\UseCase\Stats;

use App\Application\Dto\GetStatsResult;
use App\Application\Query\StatsQuery;
use App\Application\UseCase\AbstractUseCase;

class GetStatsUseCase extends AbstractUseCase
{
    public function execute(StatsQuery $query): GetStatsResult
    {
        $this->eventRepository->invalidateCaches();
        $stats = $this->eventRepository->getStats(
            $query->limit,
            $query->from?->format('Y-m-d H:i:s'),
            $query->to?->format('Y-m-d H:i:s'),
            $query->type
        );

        return new GetStatsResult(
            totalEvents: $stats['total_events'],
            uniqueUsers: $stats['unique_users'],
            topPages: $stats['top_pages']
        );
    }
}
