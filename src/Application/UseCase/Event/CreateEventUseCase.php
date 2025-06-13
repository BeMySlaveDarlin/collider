<?php

declare(strict_types=1);

namespace App\Application\UseCase\Event;

use App\Application\Command\CreateEventCommand;
use App\Application\UseCase\AbstractUseCase;
use Swoole\Coroutine;

class CreateEventUseCase extends AbstractUseCase
{
    public function execute(CreateEventCommand $query): array
    {
        $eventData = [
            'id' => $this->idGenerator->generate(),
            'user_id' => $this->getUserId($query->userId),
            'type_id' => $this->getEventTypeId($query->eventType),
            'timestamp' => $query->timestamp->format('Y-m-d H:i:s'),
            'metadata' => $query->metadata,
        ];

        Coroutine::create(function () use ($eventData, $query) {
            $this->eventTypeRepository->findOrCreate($query->eventType, $eventData['type_id']);
            $this->userRepository->findOrCreate($eventData['user_id']);
            $this->eventRepository->create($eventData);
        });

        return $eventData;
    }
}
