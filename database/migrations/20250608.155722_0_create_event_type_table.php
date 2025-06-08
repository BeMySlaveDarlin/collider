<?php

declare(strict_types=1);

namespace App;

use Cycle\Migrations\Migration;

class CreateEventTypeTableMigration extends Migration
{
    public function up(): void
    {
        $this
            ->table('users')
            ->addColumn('id', 'bigPrimary')
            ->addColumn('name', 'string', ['size' => 255, 'nullable' => false])
            ->addIndex(['name'], ['unique' => true, 'name' => 'idx_event_types_name_unique'])
            ->create();
    }

    public function down(): void
    {
        $this->table('users')->drop();
    }
}
