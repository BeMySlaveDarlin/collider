<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\User;

use App\Domain\UserAnalytics\Entity\User;
use App\Domain\UserAnalytics\Repository\UserRepository;
use App\Domain\UserAnalytics\ValueObject\CreateUserRequest;
use App\Domain\UserAnalytics\ValueObject\CreateUserResponse;
use Hyperf\Di\Annotation\Inject;

class CreateUserUseCase
{
    #[Inject]
    protected UserRepository $userRepository;

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
