<?php

declare(strict_types=1);

namespace App\Infrastructure\Endpoint\Web;

use App\Application\Factory\QueryFactory;
use App\Application\UseCase\User\CreateUserUseCase;
use App\Application\UseCase\User\GetUserEventsUseCase;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ServerRequestInterface;

class UserController extends AbstractController
{
    #[Inject]
    protected GetUserEventsUseCase $getUserEventsUseCase;
    #[Inject]
    protected CreateUserUseCase $createUserUseCase;
    #[Inject]
    protected QueryFactory $queryFactory;

    public function create(): array
    {
        $user = $this->createUserUseCase->execute();

        return [
            'data' => $user,
        ];
    }

    public function events(ServerRequestInterface $request, int $userId): array
    {
        $query = $request->getQueryParams();
        $result = $this->getUserEventsUseCase->execute(
            $this->queryFactory->getUserEventsQuery(
                $query,
                $userId
            )
        );

        return [
            'data' => $result->events,
            'query' => [
                'page' => 1,
                'limit' => $result->limit,
                'total' => $result->total,
            ],
        ];
    }
}
