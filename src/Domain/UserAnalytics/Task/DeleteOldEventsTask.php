<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Task;

use Cycle\Database\DatabaseInterface;

readonly class DeleteOldEventsTask implements TaskInterface
{
    public function __construct(
        private DatabaseInterface $database
    ) {
    }

    public function run(array $data = []): void
    {
        $before = $data['before'] ?? null;
        if ($before === null) {
            return;
        }

        $deleteSql = "DELETE FROM events WHERE timestamp < ?";

        $deleted = $this->database->execute($deleteSql, [$before]);

        echo "[Task] Deleted $deleted old events\n";
    }
}
