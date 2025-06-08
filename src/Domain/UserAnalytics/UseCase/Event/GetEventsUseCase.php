<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\Event;

use App\Domain\UserAnalytics\Entity\Event;
use App\Domain\UserAnalytics\Repository\EventRepository;
use App\Domain\UserAnalytics\ValueObject\GetEventsRequest;
use App\Domain\UserAnalytics\ValueObject\GetEventsResponse;
use Cycle\ORM\ORMInterface;

final readonly class GetEventsUseCase
{
    public function __construct(
        private ORMInterface $orm
    ) {
    }

    public function execute(GetEventsRequest $request): GetEventsResponse
    {
        $offset = ($request->page - 1) * $request->limit;

        /** @var EventRepository $eventRepository */
        $eventRepository = $this->orm->getRepository(Event::class);
        $events = $eventRepository->findWithPagination(
            $request->limit,
            $offset
        );

        $total = $eventRepository->countAll();

        return new GetEventsResponse(
            events: $events,
            page: $request->page,
            limit: $request->limit,
            total: $total
        );
    }
}
