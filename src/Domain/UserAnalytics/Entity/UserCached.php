<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Entity;

class UserCached implements UserEntityInterface
{
    public int $id;

    public string $name;
}
