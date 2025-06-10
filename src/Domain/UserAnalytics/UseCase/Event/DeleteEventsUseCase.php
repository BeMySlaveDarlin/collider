<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\Event;

use App\Domain\UserAnalytics\Repository\CachedEventRepository;
use App\Domain\UserAnalytics\Task\DeleteOldEventsTask;
use App\Domain\UserAnalytics\ValueObject\DeleteEventsRequest;
use App\Infra\Swoole\Server;

final readonly class DeleteEventsUseCase
{
    public function __construct(
        private CachedEventRepository $eventRepository,
        private Server $server,
    ) {
    }

    public function execute(DeleteEventsRequest $request): int
    {
        $count = $this->eventRepository->findBeforeDate($request->before);

        $this->server->dispatchTask([
            'taskHandlerClass' => DeleteOldEventsTask::class,
            'before' => $request->before->format('Y-m-d H:i:s'),
        ]);

        return $count;
    }
}
