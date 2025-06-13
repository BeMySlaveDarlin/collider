<?php

declare(strict_types=1);

namespace App\Infrastructure\Endpoint\Web;

use App\Application\UseCase\User\CreateUserUseCase;
use App\Application\UseCase\User\GetUserEventsUseCase;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Psr\Http\Message\ServerRequestInterface;

#[Controller]
class UserEndpoint extends AbstractEndpoint
{
    #[Inject]
    protected GetUserEventsUseCase $getUserEventsUseCase;
    #[Inject]
    protected CreateUserUseCase $createUserUseCase;

    #[PostMapping('/users')]
    public function create(): array
    {
        $user = $this->createUserUseCase->execute();

        return [
            'data' => $user,
        ];
    }

    #[GetMapping('/users/{userId}/events')]
    public function events(ServerRequestInterface $request, int $userId): array
    {
        $query = $this->queryFactory->getUserEventsQuery(
            $request->getQueryParams(),
            $userId
        );

        $result = $this->getUserEventsUseCase->execute($query);

        return [
            'data' => $result->events,
            'query' => [
                'limit' => $result->limit,
                'total' => $result->total,
            ],
        ];
    }
}
