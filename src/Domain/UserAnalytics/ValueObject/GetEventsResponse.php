<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\ValueObject;

use App\Domain\UserAnalytics\Entity\Event;

final readonly class GetEventsResponse
{
    /**
     * @param Event[] $events
     */
    public function __construct(
        public array $events,
        public int $page,
        public int $limit,
        public int $total
    ) {
    }
}
