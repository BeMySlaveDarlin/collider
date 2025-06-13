<?php

declare(strict_types=1);

namespace App\Application\Factory;

use App\Application\Command\DeleteEventsCommand;
use App\Application\Exception\InvalidArgumentException;
use App\Application\Query\EventsQuery;
use App\Application\Query\StatsQuery;
use App\Application\Query\UserEventsQuery;
use DateTimeImmutable;
use Throwable;

class QueryFactory
{
    public function getEventsQuery(array $query): EventsQuery
    {
        $page = max(1, (int) ($query['page'] ?? 1));
        $limit = max(1, (int) ($query['limit'] ?? 1));

        return new EventsQuery(
            page: $page,
            limit: $limit
        );
    }

    public function getUserEventsQuery(array $query, int $userId): UserEventsQuery
    {
        $limit = max(1, (int) ($query['limit'] ?? 1000));

        return new UserEventsQuery(
            userId: $userId,
            limit: $limit
        );
    }

    public function getStatsQuery(array $query): StatsQuery
    {
        $limit = isset($query['limit']) ? (int) $query['limit'] : 3;
        if ($limit <= 0) {
            throw new InvalidArgumentException('Limit must be a positive integer.');
        }

        $from = $this->parseDate($query['from'] ?? null, 'from');
        $to = $this->parseDate($query['to'] ?? null, 'to');

        if ($from && $to && $from > $to) {
            throw new InvalidArgumentException('"from" date cannot be after "to" date.');
        }

        $type = isset($query['type']) ? (string) $query['type'] : null;

        return new StatsQuery(
            limit: $limit,
            from: $from,
            to: $to,
            type: $type
        );
    }

    public function getDeleteEventsQuery(array $query): DeleteEventsCommand
    {
        if (empty($query['before'])) {
            throw new InvalidArgumentException('Parameter "before" is missing');
        }

        try {
            $before = new DateTimeImmutable($query['before']);
        } catch (Throwable $e) {
            throw new InvalidArgumentException('Invalid date format for "before"');
        }

        return new DeleteEventsCommand($before);
    }

    private function parseDate(?string $dateString, string $fieldName): ?DateTimeImmutable
    {
        if ($dateString === null) {
            return null;
        }

        try {
            return new DateTimeImmutable($dateString);
        } catch (Throwable $e) {
            throw new InvalidArgumentException("Invalid date format for '{$fieldName}'");
        }
    }
}
