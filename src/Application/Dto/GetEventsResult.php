<?php

declare(strict_types=1);

namespace App\Application\Dto;

class GetEventsResult
{
    public function __construct(
        public array $events,
        public int $page,
        public int $limit,
        public int $total
    ) {
    }
}
