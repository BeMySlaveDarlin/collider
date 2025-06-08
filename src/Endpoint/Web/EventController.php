<?php

declare(strict_types=1);

namespace App\Endpoint\Web;

use App\Domain\UserAnalytics\UseCase\Event\CreateEventUseCase;
use App\Domain\UserAnalytics\UseCase\Event\DeleteEventsUseCase;
use App\Domain\UserAnalytics\UseCase\Event\GetEventsUseCase;
use App\Domain\UserAnalytics\ValueObject\CreateEventRequest;
use App\Domain\UserAnalytics\ValueObject\DeleteEventsRequest;
use App\Domain\UserAnalytics\ValueObject\GetEventsRequest;
use DateTimeImmutable;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Http\ResponseWrapper;
use Spiral\Router\Annotation\Route;

final readonly class EventController
{
    public function __construct(
        private ResponseWrapper $response,
        private CreateEventUseCase $createEventUseCase,
        private GetEventsUseCase $getEventsUseCase,
        private DeleteEventsUseCase $deleteEventsUseCase
    ) {
    }

    #[Route(route: '/events', name: 'event.create', methods: ['POST'])]
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        if (!isset($data['user_id'], $data['event_type'], $data['timestamp'])) {
            return $this->response->json([
                'error' => 'Missing required fields: user_id, event_type, timestamp',
            ], 400);
        }

        try {
            $createRequest = new CreateEventRequest(
                userId: $data['user_id'],
                eventType: $data['event_type'],
                timestamp: new DateTimeImmutable($data['timestamp']),
                metadata: $data['metadata'] ?? []
            );

            $event = $this->createEventUseCase->execute($createRequest);

            return $this->response->json([
                'id' => $event->id,
                'user_id' => $event->user_id,
                'type' => $event->type->name,
                'timestamp' => $event->timestamp->format('c'),
                'metadata' => $event->metadata,
            ], 201);
        } catch (Exception $e) {
            return $this->response->json([
                'error' => 'Invalid request data: ' . $e->getMessage(),
            ], 400);
        }
    }

    #[Route(route: '/events', name: 'events.list', methods: ['GET'])]
    public function list(ServerRequestInterface $request): ResponseInterface
    {
        $query = $request->getQueryParams();

        $page = max(1, (int)($query['page'] ?? 1));
        $limit = min(1000, max(1, (int)($query['limit'] ?? 100)));

        $getRequest = new GetEventsRequest(
            page: $page,
            limit: $limit
        );

        try {
            $result = $this->getEventsUseCase->execute($getRequest);

            return $this->response->json([
                'data' => array_map(static fn($event) => [
                    'id' => $event['id'],
                    'user_id' => $event['user_id'],
                    'type' => $event['event_type'],
                    'timestamp' => $event['timestamp'],
                    'metadata' => json_decode($event['metadata'], true, 512, JSON_THROW_ON_ERROR),
                ], $result->events),
                'pagination' => [
                    'page' => $result->page,
                    'limit' => $result->limit,
                    'total' => $result->total,
                ],
            ]);
        } catch (Exception $e) {
            return $this->response->json([
                'error' => 'Failed to retrieve events: ' . $e->getMessage(),
            ], 500);
        }
    }

    #[Route(route: '/events', name: 'event.delete', methods: ['DELETE'])]
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $query = $request->getQueryParams();

        if (!isset($query['before'])) {
            return $this->response->json([
                'error' => 'Parameter "before" is required',
            ], 400);
        }

        try {
            $deleteRequest = new DeleteEventsRequest(
                before: new DateTimeImmutable($query['before'])
            );

            $deletedCount = $this->deleteEventsUseCase->execute($deleteRequest);

            return $this->response->json([
                'deleted_count' => $deletedCount,
            ]);
        } catch (Exception $e) {
            return $this->response->json([
                'error' => 'Invalid date format or deletion failed: ' . $e->getMessage(),
            ], 400);
        }
    }
}
