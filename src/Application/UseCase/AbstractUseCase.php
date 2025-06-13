<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\Exception\InvalidArgumentException;
use App\Infrastructure\Config\EventsConfig;
use App\Infrastructure\Generator\RandomNameGenerator;
use App\Infrastructure\Generator\SnowflakeIdGenerator;
use App\Infrastructure\Persistence\DatabaseManager;
use App\Infrastructure\Persistence\Repository\EventRepository;
use App\Infrastructure\Persistence\Repository\EventTypeRepository;
use App\Infrastructure\Persistence\Repository\UserRepository;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Di\Annotation\Inject;

class AbstractUseCase
{
    #[Inject]
    protected DatabaseManager $databaseManager;
    #[Inject]
    protected UserRepository $userRepository;
    #[Inject]
    protected EventTypeRepository $eventTypeRepository;
    #[Inject]
    protected EventRepository $eventRepository;

    #[Inject]
    protected EventsConfig $eventsConfig;
    #[Inject]
    protected RandomNameGenerator $randomNameGenerator;
    #[Inject]
    protected SnowflakeIdGenerator $idGenerator;

    #[Cacheable(prefix: 'users', value: '_by_id_#{id}', ttl: 3600)]
    public function getUserId(int $id): int
    {
        $maxUserId = $this->eventsConfig->getUsersMaxCount();

        return $id >= 1 && $id <= $maxUserId ? $id : throw new InvalidArgumentException('User not found');
    }

    #[Cacheable(prefix: 'event_types', value: '_id_by_name_#{name}', ttl: 3600)]
    public function getEventTypeId(string $name): int
    {
        $eventTypeId = $this->eventsConfig->getEventTypeId($name);

        return $eventTypeId ?? throw new InvalidArgumentException("Event type named {$name} not found");
    }
}
