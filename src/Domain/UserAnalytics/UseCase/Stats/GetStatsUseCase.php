<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\Stats;

use App\Domain\UserAnalytics\Entity\Event;
use App\Domain\UserAnalytics\Entity\EventType;
use App\Domain\UserAnalytics\Repository\EventRepository;
use App\Domain\UserAnalytics\Repository\EventTypeRepository;
use App\Domain\UserAnalytics\ValueObject\GetStatsRequest;
use App\Domain\UserAnalytics\ValueObject\GetStatsResponse;
use Cycle\ORM\ORMInterface;
use DomainException;

final readonly class GetStatsUseCase
{
    public function __construct(
        private ORMInterface $orm
    ) {
    }

    public function execute(GetStatsRequest $request): GetStatsResponse
    {
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->orm->getRepository(Event::class);
        /** @var EventTypeRepository $eventTypeRepository */
        $eventTypeRepository = $this->orm->getRepository(EventType::class);

        $eventType = null;
        if ($request->type) {
            $eventType = $eventTypeRepository->findByName($request->type);
            if ($eventType === null) {
                throw new DomainException(sprintf('Event type "%s" not found', $request->type));
            }
        }

        $stats = $eventRepository->getStats(
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
