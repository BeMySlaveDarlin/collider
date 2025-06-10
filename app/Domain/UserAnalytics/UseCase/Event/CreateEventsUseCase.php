<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\Event;

use App\Domain\UserAnalytics\Repository\EventRepository;
use App\Domain\UserAnalytics\Repository\EventTypeRepository;
use App\Domain\UserAnalytics\Repository\UserRepository;
use App\Domain\UserAnalytics\ValueObject\CreateEventsRequest;
use Hyperf\Di\Annotation\Inject;
use Swoole\Coroutine;

class CreateEventsUseCase
{
    #[Inject]
    protected UserRepository $userRepository;
    #[Inject]
    protected EventTypeRepository $eventTypeRepository;
    #[Inject]
    protected EventRepository $eventRepository;

    public function execute(CreateEventsRequest $request): bool
    {
        Coroutine::create(function () use ($request) {
            $eventTypeIdMap = $this->eventTypeRepository->getNameToIdMap();
            $userIds = $this->userRepository->getAllIds();

            $values = [];
            foreach ($request->events as $event) {
                if (!isset($userIds[$event->userId])) {
                    continue;
                }
                if (!isset($eventTypeIdMap[$event->eventType])) {
                    continue;
                }

                $values[] = $event->userId;
                $values[] = $eventTypeIdMap[$event->eventType];
                $values[] = $event->timestamp->format('Y-m-d H:i:s');
                $values[] = json_encode($event->metadata);
            }

            $this->eventRepository->batchInsert($values);
        });

        return true;
    }
}
