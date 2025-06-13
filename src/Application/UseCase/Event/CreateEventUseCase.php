<?php

declare(strict_types=1);

namespace App\Application\UseCase\Event;

use App\Application\Command\CreateEventCommand;
use App\Application\Exception\NotFoundException;
use App\Domain\UserAnalytics\Entity\Event;
use App\Domain\UserAnalytics\Entity\EventType;
use App\Infrastructure\Persistence\Repository\EventRepository;
use App\Infrastructure\Persistence\Repository\EventTypeRepository;
use App\Infrastructure\Persistence\Repository\UserRepository;
use Hyperf\Di\Annotation\Inject;

class CreateEventUseCase
{
    #[Inject]
    protected UserRepository $userRepository;
    #[Inject]
    protected EventTypeRepository $eventTypeRepository;
    #[Inject]
    protected EventRepository $eventRepository;

    public function execute(CreateEventCommand $query): Event
    {
        $user = $this->userRepository->findById($query->userId);
        if (!$user) {
            throw new NotFoundException('User not found');
        }

        $eventTypeId = $this->eventTypeRepository->findIdByName($query->eventType);
        if ($eventTypeId === null) {
            $eventType = new EventType();
            $eventType->name = $query->eventType;
            $this->eventTypeRepository->save($eventType);

            $eventTypeId = $eventType->id;
        }

        $event = new Event();
        $event->user_id = $query->userId;
        $event->type_id = $eventTypeId;
        $event->timestamp = $query->timestamp;
        $event->metadata = $query->metadata;

        $this->eventRepository->save($event);

        return $event;
    }
}
