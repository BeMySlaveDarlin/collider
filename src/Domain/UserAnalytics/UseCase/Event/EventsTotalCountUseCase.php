<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\Event;

use App\Domain\UserAnalytics\Repository\CachedEventRepository;
use App\Domain\UserAnalytics\ValueObject\GetEventsResponse;

final readonly class EventsTotalCountUseCase
{
    public function __construct(
        private CachedEventRepository $eventRepository
    ) {
    }

    public function execute(): GetEventsResponse
    {
        $total = $this->eventRepository->countAll();

        return new GetEventsResponse(
            events: [],
            page: 1,
            limit: 1,
            total: $total
        );
    }
}
