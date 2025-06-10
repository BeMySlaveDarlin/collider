<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Repository;

use App\Domain\UserAnalytics\Entity\Event;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CacheEvict;
use Hyperf\DbConnection\Db;

class EventRepository
{
    public function findById(int $id): ?Event
    {
        return Event::find($id);
    }

    #[Cacheable(prefix: 'events', value: '_count', ttl: 3600)]
    public function countAll(): int
    {
        return Event::count();
    }

    #[Cacheable(prefix: 'events', value: '_user_#{userId}_#{limit}', ttl: 3600)]
    public function findByUserId(int $userId, int $limit = 1000): array
    {
        $sql = '
            SELECT events.*, event_types.name AS type
            FROM events
            JOIN event_types ON events.type_id = event_types.id
            WHERE events.user_id = ?
            ORDER BY events.timestamp DESC
            LIMIT ?
        ';

        $rows = Db::select($sql, [$userId, $limit]);

        return array_map(static function (object $row): array {
            $arr = (array) $row;
            if (isset($arr['metadata'])) {
                $arr['metadata'] = json_decode($arr['metadata'], true, 512, JSON_THROW_ON_ERROR);
            }

            return $arr;
        }, $rows);
    }

    #[Cacheable(prefix: 'events', value: '_page_#{limit}_#{offset}', ttl: 3600)]
    public function findWithPagination(int $limit, int $offset): array
    {
        $sql = '
            SELECT events.*, event_types.name AS type
            FROM events
            JOIN event_types ON events.type_id = event_types.id
            ORDER BY events.timestamp DESC
            LIMIT ? OFFSET ?
        ';

        $rows = Db::select($sql, [$limit, $offset]);

        return array_map(static function (object $row): array {
            $arr = (array) $row;
            if (isset($arr['metadata'])) {
                $arr['metadata'] = json_decode($arr['metadata'], true, 512, JSON_THROW_ON_ERROR);
            }

            return $arr;
        }, $rows);
    }

    #[Cacheable(prefix: 'events', value: '_stats_#{limit}_#{eventTypeId}_#{from}_#{to}', ttl: 3600)]
    public function getStats(
        int $limit = 3,
        ?string $from = null,
        ?string $to = null,
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
            $parameters[] = $from;
        }

        if ($to !== null) {
            $whereConditions[] = 'e.timestamp <= ?';
            $parameters[] = $to;
        }

        $whereClause = empty($whereConditions) ? ' e.id > 0' : implode(' AND ', $whereConditions);

        $sql = "
            SELECT
                e.user_id,
                e.metadata->>'page' as page,
                COUNT(*) as page_count
            FROM events e
            WHERE {$whereClause}
            GROUP BY e.user_id, page
        ";

        $topPagesResult = Db::select($sql, $parameters);

        $users = [];
        $totalEvents = 0;
        $topPages = [];

        foreach ($topPagesResult as $row) {
            $pageCount = (int) $row->page_count;
            $totalEvents += $pageCount;
            $users[$row->user_id] = 1;

            if (!isset($topPages[$row->page])) {
                $topPages[$row->page] = 0;
            }
            $topPages[$row->page] += $pageCount;
        }

        $uniqueUsersCount = \count($users);
        arsort($topPages);
        $topPages = array_slice($topPages, 0, $limit, true);

        unset($topPagesResult, $users);

        return [
            'total_events' => $totalEvents,
            'unique_users' => $uniqueUsersCount,
            'top_pages' => $topPages,
        ];
    }

    #[Cacheable(prefix: 'events', value: '_before_#{date}', ttl: 3600)]
    public function findBeforeDate(string $date): int
    {
        $sql = 'SELECT COUNT(*) as total FROM events WHERE timestamp <= ?';

        /** @var object{total: int} $result */
        $result = Db::selectOne($sql, [$date]);

        return (int) $result->total;
    }

    public function deleteByBeforeDate(string $date): void
    {
        Event::where('timestamp', '<', $date)->delete();
        $this->invalidateCaches();
    }

    public function create(array $data): Event
    {
        $event = Event::create($data);
        $this->invalidateCaches();

        return $event;
    }

    public function save(Event $event): Event
    {
        $event->save();
        $this->invalidateCaches();

        return $event;
    }

    public function batchInsert(array $values): void
    {
        $placeholders = \array_fill(0, (int) (\count($values) / 4), '(?, ?, ?, ?)');
        $sql = 'INSERT INTO events (user_id, type_id, timestamp, metadata) VALUES ' . implode(',', $placeholders);

        $pdo = Db::connection()->getPdo();
        $statement = $pdo->prepare($sql);
        $statement->execute($values);

        $this->invalidateCaches();

        unset($sql, $placeholders);
    }

    #[CacheEvict(prefix: 'events', all: true)]
    public function invalidateCaches(): void
    {
    }
}
