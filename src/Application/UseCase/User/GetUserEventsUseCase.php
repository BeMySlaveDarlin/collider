<?php

declare(strict_types=1);

namespace App\Application\UseCase\User;

use App\Application\Dto\GetUserEventsResult;
use App\Application\Query\UserEventsQuery;
use App\Application\UseCase\AbstractUseCase;

class GetUserEventsUseCase extends AbstractUseCase
{
    public function execute(UserEventsQuery $query): GetUserEventsResult
    {
        $this->eventRepository->invalidateCaches();
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
