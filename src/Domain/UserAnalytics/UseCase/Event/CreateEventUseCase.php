<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\Event;

use App\Domain\UserAnalytics\Entity\Event;
use App\Domain\UserAnalytics\Entity\EventType;
use App\Domain\UserAnalytics\Entity\User;
use App\Domain\UserAnalytics\Repository\EventTypeRepository;
use App\Domain\UserAnalytics\Repository\UserRepository;
use App\Domain\UserAnalytics\ValueObject\CreateEventRequest;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\ORMInterface;
use DomainException;

final readonly class CreateEventUseCase
{
    public function __construct(
        private ORMInterface $orm,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function execute(CreateEventRequest $request): Event
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->orm->getRepository(User::class);
        /** @var EventTypeRepository $eventTypeRepository */
        $eventTypeRepository = $this->orm->getRepository(EventType::class);

        $user = $userRepository->findById($request->userId);
        if (!$user) {
            throw new DomainException('User not found');
        }

        $eventType = $eventTypeRepository->findByName($request->eventType);
        if (!$eventType) {
            $eventType = new EventType();
            $eventType->name = $request->eventType;
            $this->entityManager->persist($eventType);
            $this->entityManager->run();
        }

        $event = new Event();
        $event->user_id = $request->userId;
        $event->type_id = $eventType->id;
        $event->timestamp = $request->timestamp;
        $event->metadata = json_encode($request->metadata);
        $event->user = $user;
        $event->type = $eventType;

        $this->entityManager->persist($event);
        $this->entityManager->run();

        return $event;
    }
}
