<?php

declare(strict_types=1);

namespace App\Application\Factory;

use App\Application\Command\CreateEventCommand;
use App\Application\Command\CreateEventsCommand;
use App\Application\Exception\InvalidArgumentException;
use DateTimeImmutable;
use Throwable;

class CommandFactory
{
    public function createEventCommand(array $data): CreateEventCommand
    {
        if (!isset($data['user_id'], $data['event_type'], $data['timestamp'])) {
            throw new InvalidArgumentException('Please fill all the fields: user_id, event_type, timestamp');
        }

        try {
            $timestamp = new DateTimeImmutable($data['timestamp']);
        } catch (Throwable $e) {
            throw new InvalidArgumentException('Invalid timestamp format');
        }

        return new CreateEventCommand(
            userId: (int) $data['user_id'],
            eventType: (string) $data['event_type'],
            timestamp: $timestamp,
            metadata: $data['metadata'] ?? []
        );
    }

    public function createEventsCommand(array $data): CreateEventsCommand
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Batch data must be a non-empty array');
        }

        return new CreateEventsCommand($data);
    }
}
