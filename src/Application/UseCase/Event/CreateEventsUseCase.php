<?php

declare(strict_types=1);

namespace App\Application\UseCase\Event;

use App\Application\Command\CreateEventsCommand;
use App\Application\UseCase\AbstractUseCase;
use Hyperf\DbConnection\Db;
use Swoole\Coroutine;

class CreateEventsUseCase extends AbstractUseCase
{
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

                $values[] = $this->idGenerator->generate();
                $values[] = $event->userId;
                $values[] = $eventTypeIdMap[$event->eventType];
                $values[] = $event->timestamp->format('Y-m-d H:i:s');
                $values[] = json_encode($event->metadata);
            }

            $count = (int) (count($values) / 4);
            $placeholders = rtrim(str_repeat('(?, ?, ?, ?, ?),', $count), ',');
            $sql = "INSERT INTO events (id,user_id, type_id, timestamp, metadata) VALUES $placeholders";
            $pdo = Db::connection()->getPdo();
            $statement = $pdo->prepare($sql);
            $statement->execute($values);

            $this->eventRepository->invalidateCaches();

            unset($eventTypeIdMap, $userIds, $sql, $placeholders, $values);
        });

        return true;
    }
}
