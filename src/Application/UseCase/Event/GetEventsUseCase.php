<?php

declare(strict_types=1);

namespace App\Application\UseCase\Event;

use App\Application\Dto\GetEventsResult;
use App\Application\Query\EventsQuery;
use App\Infrastructure\Persistence\Repository\EventRepository;
use Hyperf\Di\Annotation\Inject;

class GetEventsUseCase
{
    #[Inject]
    protected EventRepository $eventRepository;

    public function execute(EventsQuery $query): GetEventsResult
    {
        $offset = ($query->page - 1) * $query->limit;

        $result = $this->eventRepository->findWithPagination(
            $query->limit,
            $offset
        );

        return new GetEventsResult(
            events: $result['rows'],
            page: $query->page,
            limit: $query->limit,
            total: $result['total'],
        );
    }
}
