<?php

declare(strict_types=1);

namespace App\Application\UseCase\User;

use App\Application\Dto\CreateUserResult;
use App\Application\UseCase\AbstractUseCase;
use App\Domain\UserAnalytics\Entity\User;

class CreateUserUseCase extends AbstractUseCase
{
    public function execute(): CreateUserResult
    {
        $user = new User();
        $user->name = $this->randomNameGenerator->generate();

        $this->userRepository->save($user);

        return new CreateUserResult(
            id: $user->id,
            name: $user->name
        );
    }
}
