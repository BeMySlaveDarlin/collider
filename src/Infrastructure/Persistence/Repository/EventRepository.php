<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Application\Exception\NotFoundException;
use App\Domain\UserAnalytics\Entity\Event;
use App\Domain\UserAnalytics\Repository\EventRepositoryInterface;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CacheEvict;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;

class EventRepository implements EventRepositoryInterface
{
    #[Inject]
    protected UserRepository $userRepository;
    #[Inject]
    protected EventTypeRepository $eventTypeRepository;

    #[Cacheable(prefix: 'events', value: '_count', ttl: 3600)]
    public function countAll(): int
    {
        return (int) Db::connection()
            ->getPdo()
            ->query('SELECT COUNT(*) FROM events')
            ->fetchColumn();
    }

    #[Cacheable(prefix: 'events', value: '_user_#{userId}_#{limit}', ttl: 3600)]
    public function findByUserId(int $userId, int $limit = 1000): array
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new NotFoundException('User not found');
        }

        $sql = '
            SELECT events.*, event_types.name AS type
            FROM events
            JOIN event_types ON events.type_id = event_types.id
            WHERE events.user_id = ?
            ORDER BY events.timestamp DESC
            LIMIT ?
        ';

        $rows = Db::select($sql, [$userId, $limit]);
        $total = $this->countAll();

        return $this->prepareResult($rows, $total);
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
        $total = $this->countAll();

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
            $eventTypeId = $this->eventTypeRepository->findIdByName($eventType);
            if ($eventTypeId === null) {
                throw new NotFoundException(sprintf('Event type "%s" not found', $eventType));
            }
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

    #[CacheEvict(prefix: 'events', all: true)]
    public function invalidateCaches(): void
    {
    }

    private function prepareResult(array $rows, int $total = 0): array
    {
        foreach ($rows as &$row) {
            if (isset($row->metadata)) {
                $row->metadata = json_decode($row->metadata, true, 512, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        }
        unset($row);

        return [
            'total' => $total,
            'rows' => $rows,
        ];
    }
}
