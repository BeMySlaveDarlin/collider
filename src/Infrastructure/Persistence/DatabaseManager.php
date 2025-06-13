<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use Hyperf\DbConnection\Db;
use PDO;
use PDOStatement;

final readonly class DatabaseManager
{
    private readonly PDO $connection;

    public function __construct()
    {
        $this->connection = Db::connection()->getPdo();
    }

    public function truncateAllTables(): void
    {
        Db::statement('TRUNCATE TABLE events RESTART IDENTITY CASCADE;');
        Db::statement('TRUNCATE TABLE event_types RESTART IDENTITY CASCADE;');
        Db::statement('TRUNCATE TABLE users RESTART IDENTITY CASCADE;');
    }

    public function optimizeForBulkInserts(): void
    {
        Db::statement('ALTER TABLE events DISABLE TRIGGER ALL');
        Db::statement('DROP INDEX CONCURRENTLY IF EXISTS idx_events_count');
        Db::statement('DROP INDEX CONCURRENTLY IF EXISTS idx_events_user_timestamp');
        Db::statement('DROP INDEX CONCURRENTLY IF EXISTS idx_events_timestamp_desc');
        Db::statement('DROP INDEX CONCURRENTLY IF EXISTS idx_events_type_timestamp');
        Db::statement('DROP INDEX CONCURRENTLY IF EXISTS idx_events_stats');
        Db::statement('DROP INDEX CONCURRENTLY IF EXISTS idx_events_covering');
    }

    public function restoreDefaultSettings(): void
    {
        Db::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_events_count ON events USING btree (id)');
        Db::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_events_user_timestamp ON events (user_id, timestamp DESC)');
        Db::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_events_timestamp_desc ON events (timestamp DESC)');
        Db::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_events_type_timestamp ON events (type_id, timestamp DESC)');
        Db::statement("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_events_stats ON events (user_id, ((metadata->>'page')), type_id)");
        Db::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_events_covering ON events (user_id, type_id, timestamp DESC) INCLUDE (id, metadata)');
        Db::statement('ALTER TABLE events ENABLE TRIGGER ALL');
    }

    public function getEventCount(): int
    {
        /** @var PDOStatement $statement */
        $statement = $this->connection->query('SELECT COUNT(*) as count FROM events');

        return (int) $statement->fetchColumn();
    }
}
