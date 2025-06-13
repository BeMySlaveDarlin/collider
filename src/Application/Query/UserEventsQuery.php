<?php

declare(strict_types=1);

namespace App\Application\Query;

class UserEventsQuery
{
    public function __construct(
        public int $userId,
        public int $limit = 1000
    ) {
    }
}
