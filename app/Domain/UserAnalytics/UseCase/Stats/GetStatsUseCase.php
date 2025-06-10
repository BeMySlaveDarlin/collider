<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\Stats;

use App\Domain\UserAnalytics\Repository\EventRepository;
use App\Domain\UserAnalytics\Repository\EventTypeRepository;
use App\Domain\UserAnalytics\ValueObject\GetStatsRequest;
use App\Domain\UserAnalytics\ValueObject\GetStatsResponse;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Exception\NotFoundHttpException;

class GetStatsUseCase
{
    #[Inject]
    protected EventTypeRepository $eventTypeRepository;
    #[Inject]
    protected EventRepository $eventRepository;

    public function execute(GetStatsRequest $request): GetStatsResponse
    {
        $eventType = null;
        if ($request->type) {
            $eventType = $this->eventTypeRepository->findByName($request->type);
            if ($eventType === null) {
                throw new NotFoundHttpException(sprintf('Event type "%s" not found', $request->type));
            }
        }

        $stats = $this->eventRepository->getStats(
            $request->limit,
            $request->from?->format('Y-m-d H:i:s'),
            $request->to?->format('Y-m-d H:i:s'),
            $eventType?->id
        );

        return new GetStatsResponse(
            totalEvents: $stats['total_events'],
            uniqueUsers: $stats['unique_users'],
            topPages: $stats['top_pages']
        );
    }
}
