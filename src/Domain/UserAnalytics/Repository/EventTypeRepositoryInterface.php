<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Repository;

use App\Domain\UserAnalytics\Entity\EventType;

interface EventTypeRepositoryInterface extends CachedRepositoryInterface
{
    public function findIdByName(string $name): ?int;

    public function getNameToIdMap(): array;

    public function save(EventType $eventType): EventType;
}
