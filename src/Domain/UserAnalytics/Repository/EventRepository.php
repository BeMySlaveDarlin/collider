<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Repository;

use Cycle\Database\DatabaseInterface;
use Cycle\Database\StatementInterface;
use Cycle\ORM\RepositoryInterface;
use DateTimeInterface;

readonly class EventRepository implements RepositoryInterface
{
    public function __construct(
        private DatabaseInterface $database
    ) {
    }

    public function findByUserId(int $userId, int $limit = 1000): array
    {
        $sql = "
            SELECT
                e.id,
                e.user_id,
                et.name as type,
                e.timestamp AT TIME ZONE 'UTC' AS timestamp,
                e.metadata
            FROM events e
            LEFT JOIN event_types et ON e.type_id = et.id
            WHERE e.user_id = ?
            ORDER BY e.timestamp DESC
            LIMIT ?
        ";

        return $this->database->query($sql, [$userId, $limit])->fetchAll();
    }

    public function findWithPagination(int $limit, int $offset): array
    {
        $sql = "
            SELECT
                e.id,
                e.user_id,
                et.name as type,
                e.timestamp AT TIME ZONE 'UTC' AS timestamp,
                e.metadata
            FROM events e
            LEFT JOIN event_types et ON e.type_id = et.id
            ORDER BY e.timestamp DESC
            LIMIT ? OFFSET ?
        ";

        return $this->database->query($sql, [$limit, $offset])->fetchAll();
    }

    public function findBeforeDate(DateTimeInterface $date): int
    {
        $sql = 'SELECT COUNT(*) as total FROM events WHERE timestamp <= ?';

        return (int)$this->database->query($sql, [$date->format('Y-m-d H:i:s')])->fetch()['total'];
    }

    public function getStats(
        int $limit = 3,
        ?DateTimeInterface $from = null,
        ?DateTimeInterface $to = null,
        ?int $eventTypeId = null
    ): array {
        $whereConditions = [];
        $parameters = [];

        if ($eventTypeId !== null) {
            $whereConditions[] = 'e.type_id = ?';
            $parameters[] = $eventTypeId;
        }

        if ($from !== null) {
            $whereConditions[] = 'e.timestamp >= ?';
            $parameters[] = $from->format('Y-m-d H:i:s');
        }

        if ($to !== null) {
            $whereConditions[] = 'e.timestamp <= ?';
            $parameters[] = $to->format('Y-m-d H:i:s');
        }

        $whereClause = empty($whereConditions) ? ' e.id > 0' : implode(' AND ', $whereConditions);

        $totalEventsSql = "
            SELECT COUNT(*) as total_events
            FROM events e
            WHERE {$whereClause}
        ";

        $totalEventsResult = $this->database->query($totalEventsSql, $parameters)->fetch();
        $totalEvents = (int)$totalEventsResult['total_events'];

        $uniqueUsersSql = "
            SELECT COUNT(DISTINCT e.user_id) as unique_users
            FROM events e
            WHERE {$whereClause}
        ";

        $uniqueUsersResult = $this->database->query($uniqueUsersSql, $parameters)->fetch();
        $uniqueUsers = (int)$uniqueUsersResult['unique_users'];

        $topPagesSql = "
            SELECT
                e.metadata->>'page' as page,
                COUNT(*) as page_count
            FROM events e
            WHERE {$whereClause}
            GROUP BY e.metadata->>'page'
            ORDER BY page_count DESC
            LIMIT $limit
        ";

        $topPagesResult = $this->database->query($topPagesSql, $parameters)->fetchAll();

        $topPages = [];
        foreach ($topPagesResult as $row) {
            $page = trim($row['page'], '"') ?: 'unknown';
            $topPages[$page] = (int)$row['page_count'];
        }

        return [
            'total_events' => $totalEvents,
            'unique_users' => $uniqueUsers,
            'top_pages' => $topPages,
        ];
    }

    public function countAll(): int
    {
        $result = $this->database
            ->query('SELECT COUNT(*) as total FROM events')
            ->fetch();

        return (int)($result['total'] ?? 0);
    }

    public function findByPK(mixed $id): ?object
    {
        $sql = "
            SELECT
                e.id,
                e.user_id,
                e.type_id,
                e.timestamp AT TIME ZONE 'UTC' AS timestamp,
                e.metadata,
                et.name as event_type
            FROM events e
            LEFT JOIN event_types et ON e.type_id = et.id
            WHERE e.id = ?
            LIMIT 1
        ";

        $result = $this->database->query($sql, [$id])->fetch(StatementInterface::FETCH_OBJ);

        return $result !== false ? $result : null;
    }

    public function findOne(array $scope = []): ?object
    {
        if (empty($scope)) {
            return null;
        }

        $conditions = [];
        $parameters = [];

        foreach ($scope as $key => $value) {
            $conditions[] = "e.{$key} = ?";
            $parameters[] = $value;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $conditions);

        $sql = "
            SELECT
                e.id,
                e.user_id,
                e.type_id,
                e.timestamp AT TIME ZONE 'UTC' AS timestamp,
                e.metadata,
                et.name as event_type
            FROM events e
            LEFT JOIN event_types et ON e.type_id = et.id
            {$whereClause}
            LIMIT 1
        ";

        $result = $this->database->query($sql, $parameters)->fetch(StatementInterface::FETCH_OBJ);

        return $result !== false ? $result : null;
    }

    public function findAll(array $scope = []): iterable
    {
        $conditions = [];
        $parameters = [];

        if (!empty($scope)) {
            foreach ($scope as $key => $value) {
                $conditions[] = "e.{$key} = ?";
                $parameters[] = $value;
            }
        }

        $whereClause = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);

        $sql = "
            SELECT
                e.id,
                e.user_id,
                e.type_id,
                e.timestamp AT TIME ZONE 'UTC' AS timestamp,
                e.metadata,
                et.name as event_type
            FROM events e
            LEFT JOIN event_types et ON e.type_id = et.id
            {$whereClause}
            ORDER BY e.timestamp DESC
        ";

        return $this->database->query($sql, $parameters)->fetchAll();
    }
}
