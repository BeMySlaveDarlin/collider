<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Entity;

use App\Domain\UserAnalytics\Repository\EventTypeRepository;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Table\Index;

#[Entity(repository: EventTypeRepository::class, table: 'event_types')]
#[Index(columns: ['name'], unique: true, name: 'idx_event_types_name_unique')]
class EventType
{
    #[Column(type: 'bigInteger', primary: true, autoIncrement: true)]
    public int $id;

    #[Column(type: 'string')]
    public string $name;
}
