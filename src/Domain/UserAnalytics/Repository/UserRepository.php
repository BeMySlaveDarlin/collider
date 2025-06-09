<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Repository;

use App\Domain\UserAnalytics\Entity\User;
use Cycle\ORM\Select\Repository;

class UserRepository extends Repository
{
    public function findById(int $id): ?User
    {
        return $this->findByPK($id);
    }

    public function findByName(string $name): ?User
    {
        return $this->findOne(['name' => $name]);
    }
}
