<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Entity;

use Carbon\Carbon;
use DateTimeInterface;
use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $type_id
 * @property Carbon|DateTimeInterface $timestamp
 * @property array|null $metadata
 */
class Event extends Model
{
    protected ?string $table = 'events';

    public bool $timestamps = false;

    protected array $fillable = [
        'user_id',
        'type_id',
        'timestamp',
        'metadata',
    ];

    protected array $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'type_id' => 'integer',
        'timestamp' => 'datetime',
        'metadata' => 'array',
    ];
}
