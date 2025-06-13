<?php

declare(strict_types=1);

namespace App\Application\Query;

class EventsQuery
{
    public function __construct(
        public int $page = 1,
        public int $limit = 100
    ) {
    }
}
