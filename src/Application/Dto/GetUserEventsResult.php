<?php

declare(strict_types=1);

namespace App\Application\Dto;

class GetUserEventsResult
{
    public function __construct(
        public array $events,
        public int $limit,
        public int $total,
    ) {
    }
}
