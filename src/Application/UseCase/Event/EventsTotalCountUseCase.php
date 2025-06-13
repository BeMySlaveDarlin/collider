<?php

declare(strict_types=1);

namespace App\Application\UseCase\Event;

use App\Application\Dto\GetEventsResult;
use App\Application\UseCase\AbstractUseCase;

class EventsTotalCountUseCase extends AbstractUseCase
{
    public function execute(): GetEventsResult
    {
        $this->eventRepository->invalidateCaches();
        $total = $this->eventRepository->countAll();

        return new GetEventsResult(
            events: [],
            page: 1,
            limit: 1,
            total: $total
        );
    }
}
