<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Entity;

class EventTypeCached implements EventTypeEntityInterface
{
    public int $id;

    public string $name;
}
