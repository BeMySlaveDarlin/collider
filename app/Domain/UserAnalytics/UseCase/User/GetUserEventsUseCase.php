<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\User;

use App\Domain\UserAnalytics\Repository\EventRepository;
use App\Domain\UserAnalytics\Repository\UserRepository;
use App\Domain\UserAnalytics\ValueObject\GetUserEventsRequest;
use App\Domain\UserAnalytics\ValueObject\GetUserEventsResponse;
use DomainException;
use Hyperf\Di\Annotation\Inject;

class GetUserEventsUseCase
{
    #[Inject]
    protected UserRepository $userRepository;
    #[Inject]
    protected EventRepository $eventRepository;

    public function execute(GetUserEventsRequest $request): GetUserEventsResponse
    {
        $user = $this->userRepository->findById($request->userId);
        if (!$user) {
            throw new DomainException('User not found');
        }

        $events = $this->eventRepository->findByUserId(
            $request->userId,
            $request->limit
        );

        return new GetUserEventsResponse(
            events: $events
        );
    }
}
