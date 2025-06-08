<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Repository;

use App\Domain\UserAnalytics\Entity\EventType;
use Cycle\ORM\Select\Repository;

class EventTypeRepository extends Repository
{
    public function findByName(string $name): ?EventType
    {
        return $this->findOne(['name' => $name]);
    }
}
