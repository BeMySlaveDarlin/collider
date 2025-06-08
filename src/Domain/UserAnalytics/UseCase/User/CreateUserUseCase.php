<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\User;

use App\Domain\UserAnalytics\Entity\User;
use App\Domain\UserAnalytics\ValueObject\CreateUserRequest;
use App\Domain\UserAnalytics\ValueObject\CreateUserResponse;
use Cycle\ORM\EntityManagerInterface;

final readonly class CreateUserUseCase
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function execute(CreateUserRequest $request): CreateUserResponse
    {
        $user = new User();
        $user->name = $request->name;

        $this->entityManager->persist($user);
        $this->entityManager->run();

        return new CreateUserResponse(
            id: $user->id,
            name: $user->name
        );
    }
}
