<?php

declare(strict_types=1);

namespace App\Application\Query;

use DateTimeImmutable;

class StatsQuery
{
    public function __construct(
        public int $limit = 3,
        public ?DateTimeImmutable $from = null,
        public ?DateTimeImmutable $to = null,
        public ?string $type = null
    ) {
    }
}
