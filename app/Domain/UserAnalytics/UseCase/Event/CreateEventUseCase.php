<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\Event;

use App\Domain\UserAnalytics\Entity\Event;
use App\Domain\UserAnalytics\Entity\EventType;
use App\Domain\UserAnalytics\Repository\EventRepository;
use App\Domain\UserAnalytics\Repository\EventTypeRepository;
use App\Domain\UserAnalytics\Repository\UserRepository;
use App\Domain\UserAnalytics\ValueObject\CreateEventRequest;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Exception\NotFoundHttpException;

class CreateEventUseCase
{
    #[Inject]
    protected UserRepository $userRepository;
    #[Inject]
    protected EventTypeRepository $eventTypeRepository;
    #[Inject]
    protected EventRepository $eventRepository;

    public function execute(CreateEventRequest $request): Event
    {
        $user = $this->userRepository->findById($request->userId);
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $eventTypeId = $this->eventTypeRepository->findIdByName($request->eventType);
        if (!$eventTypeId) {
            $eventType = new EventType();
            $eventType->name = $request->eventType;
            $this->eventTypeRepository->save($eventType);
        }

        $event = new Event();
        $event->user_id = $request->userId;
        $event->type_id = $eventTypeId;
        $event->timestamp = $request->timestamp;
        $event->metadata = $request->metadata;

        $this->eventRepository->save($event);

        return $event;
    }
}
