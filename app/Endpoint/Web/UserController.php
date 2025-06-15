<?php

declare(strict_types=1);

namespace App\Endpoint\Web;

use App\Domain\UserAnalytics\UseCase\User\CreateUserUseCase;
use App\Domain\UserAnalytics\UseCase\User\GetUserEventsUseCase;
use App\Domain\UserAnalytics\ValueObject\CreateUserRequest;
use App\Domain\UserAnalytics\ValueObject\GetUserEventsRequest;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ServerRequestInterface;

class UserController extends AbstractController
{
    #[Inject]
    protected GetUserEventsUseCase $getUserEventsUseCase;
    #[Inject]
    protected CreateUserUseCase $createUserUseCase;

    public function create(): array
    {
        $createUserRequest = new CreateUserRequest(
            name: md5(random_bytes(4))
        );

        $user = $this->createUserUseCase->execute($createUserRequest);

        return [
            'data' => $user,
        ];
    }

    public function events(ServerRequestInterface $request, int $userId): array
    {
        $query = $request->getQueryParams();
        $limit = max(1, (int) ($query['limit'] ?? 1000));

        $userEventsRequest = new GetUserEventsRequest(
            userId: $userId,
            limit: $limit
        );

        $result = $this->getUserEventsUseCase->execute($userEventsRequest);

        return [
            'data' => $result->events,
            'query' => [
                'page' => 1,
                'limit' => $limit,
                'total' => $result->total,
            ],
        ];
    }
}
