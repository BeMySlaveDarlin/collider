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

        $result = $this->eventRepository->findWithPagination(
            $request->limit,
            $offset
        );

        return new GetEventsResponse(
            events: $result['rows'],
            page: $request->page,
            limit: $request->limit,
            total: $result['total'],
        );
    }
}
