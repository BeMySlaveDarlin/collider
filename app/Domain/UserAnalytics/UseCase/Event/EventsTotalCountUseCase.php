<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\Event;

use App\Domain\UserAnalytics\Repository\EventRepository;
use App\Domain\UserAnalytics\ValueObject\GetEventsResponse;
use Hyperf\Di\Annotation\Inject;

class EventsTotalCountUseCase
{
    #[Inject]
    protected EventRepository $eventRepository;

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
