<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Repository;

interface EventTypeRepositoryInterface extends CachedRepositoryInterface
{
    public function findOrCreate(string $name, ?int $id = null): ?int;

    public function getNameToIdMap(): array;
}
