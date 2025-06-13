<?php

declare(strict_types=1);

namespace App\Application\UseCase\Event;

use App\Application\Command\CreateEventsCommand;
use App\Infrastructure\Persistence\Repository\EventRepository;
use App\Infrastructure\Persistence\Repository\EventTypeRepository;
use App\Infrastructure\Persistence\Repository\UserRepository;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Swoole\Coroutine;

class CreateEventsUseCase
{
    #[Inject]
    protected UserRepository $userRepository;
    #[Inject]
    protected EventTypeRepository $eventTypeRepository;
    #[Inject]
    protected EventRepository $eventRepository;

    public function execute(CreateEventsCommand $query): bool
    {
        Coroutine::create(function () use ($query) {
            $eventTypeIdMap = $this->eventTypeRepository->getNameToIdMap();
            $userIds = $this->userRepository->getAllIds();

            $values = [];
            foreach ($query->events as $event) {
                if (!isset($userIds[$event->userId])) {
                    continue;
                }
                if (!isset($eventTypeIdMap[$event->eventType])) {
                    continue;
                }

                $values[] = $event->userId;
                $values[] = $eventTypeIdMap[$event->eventType];
                $values[] = $event->timestamp->format('Y-m-d H:i:s');
                $values[] = json_encode($event->metadata, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }

            $count = (int) (count($values) / 4);
            $placeholders = rtrim(str_repeat('(?, ?, ?, ?),', $count), ',');
            $sql = "INSERT INTO events (user_id, type_id, timestamp, metadata) VALUES $placeholders";
            $pdo = Db::connection()->getPdo();
            $statement = $pdo->prepare($sql);
            $statement->execute($values);

            $this->eventTypeRepository->invalidateCaches();
            $this->eventRepository->invalidateCaches();

            unset($eventTypeIdMap, $userIds, $sql, $placeholders, $values);
        });

        return true;
    }
}
