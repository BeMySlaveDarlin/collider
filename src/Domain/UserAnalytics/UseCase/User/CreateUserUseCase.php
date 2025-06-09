<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\User;

use App\Domain\UserAnalytics\Entity\User;
use App\Domain\UserAnalytics\Repository\CachedUserRepository;
use App\Domain\UserAnalytics\ValueObject\CreateUserRequest;
use App\Domain\UserAnalytics\ValueObject\CreateUserResponse;

final readonly class CreateUserUseCase
{
    public function __construct(
        private CachedUserRepository $userRepository
    ) {
    }

    public function execute(CreateUserRequest $request): CreateUserResponse
    {
        $user = new User();
        $user->name = $request->name;

        $this->userRepository->save($user);

        return new CreateUserResponse(
            id: $user->id,
            name: $user->name
        );
    }
}
