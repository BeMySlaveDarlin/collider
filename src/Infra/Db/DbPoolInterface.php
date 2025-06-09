<?php

declare(strict_types=1);

namespace App\Infra\Db;

use PDO;

interface DbPoolInterface
{
    public function get(): PDO;

    public function put(PDO $connection): void;

    public function close(): void;
}
