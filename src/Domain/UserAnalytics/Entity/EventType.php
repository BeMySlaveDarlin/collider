<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Entity;

use App\Domain\UserAnalytics\Repository\EventTypeRepository;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Table\Index;
use JsonSerializable;

#[Entity(repository: EventTypeRepository::class, table: 'event_types')]
#[Index(columns: ['name'], unique: true, name: 'idx_event_types_name_unique')]
class EventType implements EventTypeEntityInterface, JsonSerializable
{
    #[Column(type: 'bigInteger', primary: true, autoIncrement: true)]
    public int $id;

    #[Column(type: 'string')]
    public string $name;

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
