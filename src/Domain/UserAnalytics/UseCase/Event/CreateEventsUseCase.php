<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\Event;

use App\Domain\UserAnalytics\Task\BatchCreateEventsTask;
use App\Domain\UserAnalytics\ValueObject\CreateEventsRequest;
use App\Infra\Swoole\Server;

final readonly class CreateEventsUseCase
{
    public function __construct(
        private Server $server,
    ) {
    }

    public function execute(CreateEventsRequest $request): bool
    {
        $this->server->dispatchTask([
            'taskHandlerClass' => BatchCreateEventsTask::class,
            'events' => $request,
        ]);

        return true;
    }
}
