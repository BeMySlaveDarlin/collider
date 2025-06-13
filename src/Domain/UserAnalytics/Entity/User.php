<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Entity;

use Carbon\Carbon;
use DateTimeInterface;
use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id
 * @property string $name
 * @property Carbon|DateTimeInterface $created_at
 */
class User extends Model
{
    protected ?string $table = 'users';

    public bool $timestamps = false;

    protected array $fillable = [
        'name',
        'created_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'name' => 'string',
        'created_at' => 'datetime',
    ];
}
