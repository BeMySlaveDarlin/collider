<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\Event;

use App\Domain\UserAnalytics\Repository\EventRepository;
use App\Domain\UserAnalytics\ValueObject\GetEventsRequest;
use App\Domain\UserAnalytics\ValueObject\GetEventsResponse;
use Hyperf\Di\Annotation\Inject;

class GetEventsUseCase
{
    #[Inject]
    protected EventRepository $eventRepository;

    public function execute(GetEventsRequest $request): GetEventsResponse
    {
        $offset = ($request->page - 1) * $request->limit;

        $events = $this->eventRepository->findWithPagination(
            $request->limit,
            $offset
        );

        $total = $this->eventRepository->countAll();

        return new GetEventsResponse(
            events: $events,
            page: $request->page,
            limit: $request->limit,
            total: $total
        );
    }
}
