<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;

class AddIndexes extends Migration
{
    public function up(): void
    {
        Schema::table('events', static function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['type_id']);
            $table->dropIndex(['timestamp']);
        });

        Db::statement("CREATE INDEX IF NOT EXISTS idx_events_count ON events USING btree (id)");
        Db::statement("CREATE INDEX IF NOT EXISTS idx_events_user_timestamp ON events (user_id, timestamp DESC)");
        Db::statement("CREATE INDEX IF NOT EXISTS idx_events_timestamp_desc ON events (timestamp DESC)");
        Db::statement("CREATE INDEX IF NOT EXISTS idx_events_type_timestamp ON events (type_id, timestamp DESC)");
        Db::statement("CREATE INDEX IF NOT EXISTS idx_events_stats ON events (user_id, ((metadata->>'page')), type_id)");
        Db::statement("CREATE INDEX IF NOT EXISTS idx_events_covering ON events (user_id, type_id, timestamp DESC) INCLUDE (id, metadata)");
    }

    public function down(): void
    {
        Db::statement("DROP INDEX IF EXISTS idx_events_count");
        Db::statement("DROP INDEX IF EXISTS idx_events_user_timestamp");
        Db::statement("DROP INDEX IF EXISTS idx_events_timestamp_desc");
        Db::statement("DROP INDEX IF EXISTS idx_events_type_timestamp");
        Db::statement("DROP INDEX IF EXISTS idx_events_stats");
        Db::statement("DROP INDEX IF EXISTS idx_events_covering");

        Schema::table('events', static function (Blueprint $table) {
            $table->index('user_id');
            $table->index('type_id');
            $table->index('timestamp');
        });
    }
}
