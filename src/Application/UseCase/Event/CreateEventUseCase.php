<?php

declare(strict_types=1);

namespace App\Application\UseCase\Event;

use App\Application\Command\CreateEventCommand;
use App\Domain\UserAnalytics\Entity\Event;
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
        $this->userRepository->findOrCreateById($query->userId);
        $eventTypeId = $this->eventTypeRepository->findOrCreateByName($query->eventType);
        $event = new Event();
        $event->user_id = $query->userId;
        $event->type_id = $eventTypeId;
        $event->timestamp = $query->timestamp;
        $event->metadata = $query->metadata;

        $this->eventRepository->save($event);

        return $event;
    }
}
