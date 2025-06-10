<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\Event;

use App\Domain\UserAnalytics\Repository\EventRepository;
use App\Domain\UserAnalytics\ValueObject\DeleteEventsRequest;
use Hyperf\Di\Annotation\Inject;
use Swoole\Coroutine;

class DeleteEventsUseCase
{
    #[Inject]
    protected EventRepository $eventRepository;

    public function execute(DeleteEventsRequest $request): int
    {
        $count = $this->eventRepository->findBeforeDate($request->before->format('Y-m-d H:i:s'));

        Coroutine::create(function () use ($request) {
            $this->eventRepository->deleteByBeforeDate($request->before->format('Y-m-d H:i:s'));
        });

        return $count;
    }
}
