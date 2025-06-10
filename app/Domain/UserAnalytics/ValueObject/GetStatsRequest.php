<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\ValueObject;

use DateTimeImmutable;

class GetStatsRequest
{
    public function __construct(
        public int $limit = 3,
        public ?DateTimeImmutable $from = null,
        public ?DateTimeImmutable $to = null,
        public ?string $type = null
    ) {
    }
}
