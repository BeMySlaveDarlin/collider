<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\Event;

use App\Domain\UserAnalytics\Entity\Event;
use App\Domain\UserAnalytics\Repository\EventRepository;
use App\Domain\UserAnalytics\Task\DeleteOldEventsTask;
use App\Domain\UserAnalytics\ValueObject\DeleteEventsRequest;
use App\Infra\Swoole\Server;
use Cycle\Database\DatabaseInterface;
use Cycle\ORM\ORMInterface;

final readonly class DeleteEventsUseCase
{
    public function __construct(
        private ORMInterface $orm,
        private DatabaseInterface $database,
        private Server $server,
    ) {
    }

    public function execute(DeleteEventsRequest $request): int
    {
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->orm->getRepository(Event::class);

        $count = $eventRepository->findBeforeDate($request->before);

        $this->server->dispatchTask([
            'taskClass' => DeleteOldEventsTask::class,
            'before' => $request->before->format('Y-m-d H:i:s'),
        ]);

        return $count;
    }
}
