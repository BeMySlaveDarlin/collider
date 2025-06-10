<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Entity;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id
 * @property string $name
 */
class EventType extends Model
{
    protected ?string $table = 'event_types';

    public bool $timestamps = false;

    protected array $fillable = [
        'name',
    ];

    protected array $casts = [
        'id' => 'integer',
        'name' => 'string',
    ];
}
