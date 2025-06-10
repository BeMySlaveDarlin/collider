<?php

declare(strict_types=1);

namespace App\Endpoint\Web;

use App\Domain\UserAnalytics\UseCase\User\CreateUserUseCase;
use App\Domain\UserAnalytics\UseCase\User\GetUserEventsUseCase;
use App\Domain\UserAnalytics\ValueObject\CreateUserRequest;
use App\Domain\UserAnalytics\ValueObject\GetUserEventsRequest;
use Exception;
use Faker\Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Http\ResponseWrapper;
use Spiral\Router\Annotation\Route;

final readonly class UserController
{
    public function __construct(
        private ResponseWrapper $response,
        private GetUserEventsUseCase $getUserEventsUseCase,
        private CreateUserUseCase $createUserUseCase
    ) {
    }

    #[Route(route: '/users', name: 'user.create', methods: ['POST'])]
    public function create(): ResponseInterface
    {
        try {
            $faker = Factory::create();
            $createUserRequest = new CreateUserRequest(
                name: $faker->name()
            );

            $user = $this->createUserUseCase->execute($createUserRequest);

            return $this->response->json([
                'data' => $user,
            ], 201);
        } catch (Exception $e) {
            return $this->response->json([
                'error' => 'Failed to create user: ' . $e->getMessage(),
            ], 500);
        }
    }

    #[Route(route: '/users/events', name: 'user.events', methods: ['GET'])]
    public function events(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $query = $request->getQueryParams();
            if (empty($query['user_id'])) {
                return $this->response->json([
                    'error' => 'User ID cannot be empty',
                ], 400);
            }

            $limit = max(1, (int)($query['limit'] ?? 1));

            $userEventsRequest = new GetUserEventsRequest(
                userId: (int)$query['user_id'],
                limit: $limit
            );

            $result = $this->getUserEventsUseCase->execute($userEventsRequest);

            return $this->response->json([
                'data' => $result->events,
                'query' => [
                    'page' => 1,
                    'limit' => $limit,
                    'total' => count($result->events),
                ],
            ]);
        } catch (Exception $e) {
            return $this->response->json([
                'error' => 'Failed to retrieve user events: ' . $e->getMessage(),
            ], 500);
        }
    }
}
