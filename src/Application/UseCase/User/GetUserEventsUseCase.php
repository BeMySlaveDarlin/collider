<?php

declare(strict_types=1);

namespace App\Application\UseCase\User;

use App\Application\Dto\GetUserEventsResult;
use App\Application\Query\UserEventsQuery;
use App\Infrastructure\Persistence\Repository\EventRepository;
use Hyperf\Di\Annotation\Inject;

class GetUserEventsUseCase
{
    #[Inject]
    protected EventRepository $eventRepository;

    public function execute(UserEventsQuery $query): GetUserEventsResult
    {
        $result = $this->eventRepository->findByUserId(
            $query->userId,
            $query->limit
        );

        return new GetUserEventsResult(
            events: $result['rows'],
            limit: $query->limit,
            total: $result['total']
        );
    }
}
