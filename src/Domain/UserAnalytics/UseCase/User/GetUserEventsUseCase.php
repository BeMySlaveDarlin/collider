<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\User;

use App\Domain\UserAnalytics\Repository\CachedEventRepository;
use App\Domain\UserAnalytics\Repository\CachedUserRepository;
use App\Domain\UserAnalytics\ValueObject\GetUserEventsRequest;
use App\Domain\UserAnalytics\ValueObject\GetUserEventsResponse;
use DomainException;

final readonly class GetUserEventsUseCase
{
    public function __construct(
        private CachedUserRepository $userRepository,
        private CachedEventRepository $eventRepository
    ) {
    }

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
            events: array_map(static function ($event) {
                $event['metadata'] = json_decode(
                    $event['metadata'],
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );

                return $event;
            }, $events)
        );
    }
}
