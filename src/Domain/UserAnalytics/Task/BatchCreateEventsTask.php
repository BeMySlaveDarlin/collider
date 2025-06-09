<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Task;

use App\Domain\UserAnalytics\Repository\CachedEventRepository;
use App\Domain\UserAnalytics\Repository\EventRepository;
use App\Domain\UserAnalytics\ValueObject\CreateEventsRequest;

readonly class BatchCreateEventsTask implements TaskInterface
{
    public function __construct(
        private CachedEventRepository $cachedEventRepository,
        private EventRepository $eventRepository
    ) {
    }

    public function run(array $data = []): void
    {
        $events = $data['events'] ?? null;
        if (!$events instanceof CreateEventsRequest) {
            return;
        }

        $created = $this->eventRepository->insertBatch($events);

        $this->cachedEventRepository->invalidateCache();

        echo "[Task] Create $created events\n";
    }
}
