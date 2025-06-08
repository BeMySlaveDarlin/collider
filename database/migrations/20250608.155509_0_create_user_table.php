<?php

declare(strict_types=1);

namespace App;

use Cycle\Migrations\Migration;

class CreateUserTableMigration extends Migration
{
    public function up(): void
    {
        $this
            ->table('event_types')
            ->addColumn('id', 'bigPrimary')
            ->addColumn('name', 'string', ['size' => 100, 'nullable' => false])
            ->addIndex(['name'], ['unique' => true, 'name' => 'idx_users_name_unique'])
            ->create();
    }

    public function down(): void
    {
        $this->table('event_types')->drop();
    }
}
