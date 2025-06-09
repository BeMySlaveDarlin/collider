<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\Event;

use App\Domain\UserAnalytics\Entity\Event;
use App\Domain\UserAnalytics\Entity\EventType;
use App\Domain\UserAnalytics\Repository\CachedEventRepository;
use App\Domain\UserAnalytics\Repository\CachedEventTypeRepository;
use App\Domain\UserAnalytics\Repository\CachedUserRepository;
use App\Domain\UserAnalytics\ValueObject\CreateEventRequest;
use DomainException;

final readonly class CreateEventUseCase
{
    public function __construct(
        private CachedEventTypeRepository $eventTypeRepository,
        private CachedUserRepository $userRepository,
        private CachedEventRepository $eventRepository
    ) {
    }

    public function execute(CreateEventRequest $request): Event
    {
        $user = $this->userRepository->findById($request->userId);
        if (!$user) {
            throw new DomainException('User not found');
        }

        $eventType = $this->eventTypeRepository->findByName($request->eventType);
        if (!$eventType) {
            $eventType = new EventType();
            $eventType->name = $request->eventType;
            $this->eventTypeRepository->save($eventType);
        }

        $event = new Event();
        $event->user_id = $request->userId;
        $event->type_id = $eventType->id;
        $event->timestamp = $request->timestamp;
        $event->metadata = json_encode($request->metadata);

        $this->eventRepository->save($event);

        return $event;
    }
}
