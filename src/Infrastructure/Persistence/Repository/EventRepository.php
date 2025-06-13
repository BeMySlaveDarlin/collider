<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\UserAnalytics\Entity\Event;
use App\Domain\UserAnalytics\Repository\EventRepositoryInterface;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Cache;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use PDO;

class EventRepository implements EventRepositoryInterface
{
    #[Inject]
    protected UserRepository $userRepository;
    #[Inject]
    protected EventTypeRepository $eventTypeRepository;
    #[Inject]
    protected Cache $cache;

    #[Cacheable(prefix: 'events', value: '_count_#{userId}', ttl: 3600)]
    public function countAll(?int $userId = null): int
    {
        $pdo = Db::connection()->getPdo();

        if ($userId !== null) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM events WHERE user_id = :userId');
            $stmt->execute(['userId' => $userId]);
        } else {
            $stmt = $pdo->query('SELECT COUNT(*) FROM events');
        }

        return (int) $stmt->fetchColumn();
    }

    #[Cacheable(prefix: 'events', value: '_page_#{limit}_#{offset}', ttl: 3600)]
    public function findWithPagination(int $limit, int $offset): array
    {
        $pdo = Db::connection()->getPdo();

        $sql = '
            SELECT events.*, event_types.name AS type
            FROM events
            JOIN event_types ON events.type_id = event_types.id
            ORDER BY events.timestamp DESC
            LIMIT ? OFFSET ?
        ';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit, $offset]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = $this->countAll();

        return $this->prepareResult($rows, $total);
    }

    #[Cacheable(prefix: 'events', value: '_user_#{userId}_#{limit}', ttl: 3600)]
    public function findByUserId(int $userId, int $limit = 1000): array
    {
        $this->userRepository->findOrCreate($userId);

        $pdo = Db::connection()->getPdo();

        $sql = '
            SELECT events.*, event_types.name AS type
            FROM events
            JOIN event_types ON events.type_id = event_types.id
            WHERE events.user_id = ?
            ORDER BY events.timestamp DESC
            LIMIT ?
        ';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $limit]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = $this->countAll($userId);

        return $this->prepareResult($rows, $total);
    }

    #[Cacheable(prefix: 'events', value: '_stats_#{limit}_#{eventTypeId}_#{from}_#{to}', ttl: 3600)]
    public function getStats(
        int $limit = 3,
        ?string $from = null,
        ?string $to = null,
        ?string $eventType = null
    ): array {
        $eventTypeId = null;
        if ($eventType) {
            $eventTypeId = $this->eventTypeRepository->findOrCreate($eventType);
        }

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

        $whereClause = empty($whereConditions) ? 'e.id > 0' : implode(' AND ', $whereConditions);

        $sql = "
            SELECT
                e.user_id,
                e.metadata->>'page' as page,
                COUNT(*) as page_count
            FROM events e
            WHERE {$whereClause}
            GROUP BY e.user_id, page
        ";

        $pdo = Db::connection()->getPdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($parameters);
        $topPagesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        $totalEvents = 0;
        $topPages = [];

        foreach ($topPagesResult as $row) {
            $pageCount = (int) $row['page_count'];
            $totalEvents += $pageCount;
            $users[$row['user_id']] = 1;

            if (!isset($topPages[$row['page']])) {
                $topPages[$row['page']] = 0;
            }
            $topPages[$row['page']] += $pageCount;
        }

        $uniqueUsersCount = count($users);
        arsort($topPages);
        $topPages = array_slice($topPages, 0, $limit, true);

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
        $this->cache->set('events_updated', false);
    }

    public function invalidateCaches(): void
    {
        if ($this->cache->has('events_updated')) {
            $this->cache->clear();
        }
    }

    private function prepareResult(array $rows, int $total = 0): array
    {
        foreach ($rows as &$row) {
            if (isset($row['metadata'])) {
                $row['metadata'] = json_decode($row['metadata'], true, 512, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        }
        unset($row);

        return [
            'total' => $total,
            'rows' => $rows,
        ];
    }

    public function create(array $eventData): void
    {
        $sql = 'INSERT INTO events (id, user_id, type_id, timestamp, metadata) VALUES (?, ?, ?, ?, ?)';
        Db::insert($sql, [
            $eventData['id'],
            $eventData['user_id'],
            $eventData['type_id'],
            $eventData['timestamp'],
            json_encode($eventData['metadata']),
        ]);
    }
}
