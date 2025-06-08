<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\User;

use App\Domain\UserAnalytics\Entity\Event;
use App\Domain\UserAnalytics\Entity\User;
use App\Domain\UserAnalytics\Repository\EventRepository;
use App\Domain\UserAnalytics\Repository\UserRepository;
use App\Domain\UserAnalytics\ValueObject\GetUserEventsRequest;
use App\Domain\UserAnalytics\ValueObject\GetUserEventsResponse;
use Cycle\ORM\ORMInterface;
use DomainException;

final readonly class GetUserEventsUseCase
{
    public function __construct(
        private ORMInterface $orm
    ) {
    }

    public function execute(GetUserEventsRequest $request): GetUserEventsResponse
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->orm->getRepository(User::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->orm->getRepository(Event::class);

        $user = $userRepository->findById($request->userId);
        if (!$user) {
            throw new DomainException('User not found');
        }

        $events = $eventRepository->findByUserId(
            $request->userId,
            $request->limit
        );

        return new GetUserEventsResponse(
            events: $events
        );
    }
}
