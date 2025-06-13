<?php

declare(strict_types=1);

namespace App\Application\UseCase\Event;

use App\Application\Command\DeleteEventsCommand;
use App\Infrastructure\Persistence\Repository\EventRepository;
use Hyperf\Di\Annotation\Inject;
use Swoole\Coroutine;

class DeleteEventsUseCase
{
    #[Inject]
    protected EventRepository $eventRepository;

    public function execute(DeleteEventsCommand $query): int
    {
        $count = $this->eventRepository->findBeforeDate($query->before->format('Y-m-d H:i:s'));

        Coroutine::create(function () use ($query) {
            $this->eventRepository->deleteByBeforeDate($query->before->format('Y-m-d H:i:s'));
        });

        return $count;
    }
}
