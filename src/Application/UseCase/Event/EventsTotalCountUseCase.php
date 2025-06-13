<?php

declare(strict_types=1);

namespace App\Application\UseCase\Event;

use App\Application\Dto\GetEventsResult;
use App\Infrastructure\Persistence\Repository\EventRepository;
use Hyperf\Di\Annotation\Inject;

class EventsTotalCountUseCase
{
    #[Inject]
    protected EventRepository $eventRepository;

    public function execute(): GetEventsResult
    {
        $total = $this->eventRepository->countAll();

        return new GetEventsResult(
            events: [],
            page: 1,
            limit: 1,
            total: $total
        );
    }
}
