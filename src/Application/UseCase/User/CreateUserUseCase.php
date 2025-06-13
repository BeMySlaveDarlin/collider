<?php

declare(strict_types=1);

namespace App\Application\UseCase\User;

use App\Application\Dto\CreateUserResult;
use App\Domain\UserAnalytics\Entity\User;
use App\Infrastructure\Persistence\Repository\UserRepository;
use App\Infrastructure\Seeding\RandomNameGenerator;
use Hyperf\Di\Annotation\Inject;

class CreateUserUseCase
{
    #[Inject]
    protected UserRepository $userRepository;
    #[Inject]
    protected RandomNameGenerator $randomNameGenerator;

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
