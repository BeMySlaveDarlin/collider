<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\Stats;

use App\Domain\UserAnalytics\Repository\CachedEventRepository;
use App\Domain\UserAnalytics\Repository\CachedEventTypeRepository;
use App\Domain\UserAnalytics\ValueObject\GetStatsRequest;
use App\Domain\UserAnalytics\ValueObject\GetStatsResponse;
use DomainException;

final readonly class GetStatsUseCase
{
    public function __construct(
        private CachedEventRepository $eventRepository,
        private CachedEventTypeRepository $eventTypeRepository
    ) {
    }

    public function execute(GetStatsRequest $request): GetStatsResponse
    {
        $eventType = null;
        if ($request->type) {
            $eventType = $this->eventTypeRepository->findByName($request->type);
            if ($eventType === null) {
                throw new DomainException(sprintf('Event type "%s" not found', $request->type));
            }
        }

        $stats = $this->eventRepository->getStats(
            $request->limit,
            $request->from,
            $request->to,
            $eventType?->id
        );

        return new GetStatsResponse(
            totalEvents: $stats['total_events'],
            uniqueUsers: $stats['unique_users'],
            topPages: $stats['top_pages']
        );
    }
}
