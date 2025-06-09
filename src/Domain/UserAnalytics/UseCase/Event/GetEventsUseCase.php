<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\Event;

use App\Domain\UserAnalytics\Repository\CachedEventRepository;
use App\Domain\UserAnalytics\ValueObject\GetEventsRequest;
use App\Domain\UserAnalytics\ValueObject\GetEventsResponse;

final readonly class GetEventsUseCase
{
    public function __construct(
        private CachedEventRepository $eventRepository
    ) {
    }

    public function execute(GetEventsRequest $request): GetEventsResponse
    {
        $offset = ($request->page - 1) * $request->limit;

        $events = $this->eventRepository->findWithPagination(
            $request->limit,
            $offset
        );

        $total = $this->eventRepository->countAll();

        return new GetEventsResponse(
            events: array_map(static function ($event) {
                $event['metadata'] = json_decode(
                    $event['metadata'],
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );

                return $event;
            }, $events),
            page: $request->page,
            limit: $request->limit,
            total: $total
        );
    }
}
