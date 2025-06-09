<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Entity;

use App\Domain\UserAnalytics\Repository\EventRepository;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use Cycle\Annotated\Annotation\Table\Index;
use DateTimeInterface;
use JsonSerializable;

#[Entity(repository: EventRepository::class, table: 'events')]
#[Index(columns: ['user_id'], name: 'idx_events_user_id')]
#[Index(columns: ['type_id'], name: 'idx_events_type_id')]
#[Index(columns: ['timestamp'], name: 'idx_events_timestamp')]
#[Index(columns: ['user_id', 'timestamp'], name: 'idx_events_user_time')]
#[Index(columns: ['type_id', 'timestamp'], name: 'idx_events_type_time')]
class Event implements JsonSerializable
{
    #[Column(type: 'bigInteger', primary: true, autoIncrement: true)]
    public int $id;

    #[Column(type: 'bigInteger')]
    public int $user_id;

    #[Column(type: 'bigInteger')]
    public int $type_id;

    #[Column(type: 'timestamp', default: 'CURRENT_TIMESTAMP')]
    public DateTimeInterface $timestamp;

    #[Column(type: 'json', nullable: true)]
    public ?string $metadata = null;

    #[BelongsTo(target: User::class)]
    public ?User $user = null;

    #[BelongsTo(target: EventType::class)]
    public ?EventType $type = null;

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'type' => $this->type->name,
            'timestamp' => $this->timestamp->format('c'),
            'metadata' => json_decode($this->metadata, true, 512, JSON_THROW_ON_ERROR),
        ];
    }
}
