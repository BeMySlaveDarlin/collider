<?php

declare(strict_types=1);

namespace App;

use Cycle\Migrations\Migration;

class CreateEventTableMigration extends Migration
{
    public function up(): void
    {
        $this
            ->table('events')
            ->addColumn('id', 'bigPrimary')
            ->addColumn('user_id', 'bigInteger', ['nullable' => false])
            ->addColumn('type_id', 'bigInteger', ['nullable' => false])
            ->addColumn('timestamp', 'timestamp', ['nullable' => false])
            ->addColumn('metadata', 'jsonb', ['nullable' => true])
            ->addForeignKey(['user_id'], 'users', ['id'], [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey(['type_id'], 'event_types', ['id'], [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addIndex(['timestamp'], ['name' => 'idx_events_timestamp'])
            ->addIndex(['user_id', 'timestamp'], ['name' => 'idx_events_user_time'])
            ->addIndex(['type_id', 'timestamp'], ['name' => 'idx_events_type_time'])
            ->create();
        $this->database()->execute("CREATE INDEX idx_events_metadata_gin ON events USING gin (metadata jsonb_path_ops);");
    }

    public function down(): void
    {
        $this->table('events')->drop();
    }
}
