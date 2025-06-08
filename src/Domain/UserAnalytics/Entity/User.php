<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Entity;

use App\Domain\UserAnalytics\Repository\UserRepository;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Table\Index;

#[Entity(repository: UserRepository::class, table: 'users')]
#[Index(columns: ['name'], unique: true, name: 'idx_users_name_unique')]
class User
{
    #[Column(type: 'bigInteger', primary: true, autoIncrement: true)]
    public int $id;

    #[Column(type: 'string')]
    public string $name;
}
