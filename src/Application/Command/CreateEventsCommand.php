<?php

declare(strict_types=1);

namespace App\Application\Command;

use DateTimeImmutable;

class CreateEventsCommand
{
    /**
     * @var CreateEventCommand[]
     */
    public array $events;

    public function __construct(array $events)
    {
        $_events = [];
        foreach ($events as $event) {
            $_events[] = new CreateEventCommand(
                userId: $event['user_id'],
                eventType: $event['event_type'],
                timestamp: new DateTimeImmutable($event['timestamp']),
                metadata: $event['metadata'],
            );
        }
        $this->events = $_events;
    }
}
