<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\ValueObject;

use DateTimeImmutable;

final readonly class CreateEventsRequest
{
    /**
     * @var CreateEventRequest[]
     */
    public array $events;

    public function __construct(array $events)
    {
        $_events = [];
        foreach ($events as $event) {
            $_events[] = new CreateEventRequest(
                userId: $event['user_id'],
                eventType: $event['event_type'],
                timestamp: new DateTimeImmutable($event['timestamp']),
                metadata: $event['metadata'],
            );
        }
        $this->events = $_events;
    }
}
