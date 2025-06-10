<?php

declare(strict_types=1);

namespace App\Endpoint\Web;

use App\Domain\UserAnalytics\UseCase\Event\CreateEventsUseCase;
use App\Domain\UserAnalytics\UseCase\Event\CreateEventUseCase;
use App\Domain\UserAnalytics\UseCase\Event\DeleteEventsUseCase;
use App\Domain\UserAnalytics\UseCase\Event\EventsTotalCountUseCase;
use App\Domain\UserAnalytics\UseCase\Event\GetEventsUseCase;
use App\Domain\UserAnalytics\ValueObject\CreateEventRequest;
use App\Domain\UserAnalytics\ValueObject\CreateEventsRequest;
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
        private CreateEventsUseCase $createEventsUseCase,
        private GetEventsUseCase $getEventsUseCase,
        private EventsTotalCountUseCase $totalEventsUseCase,
        private DeleteEventsUseCase $deleteEventsUseCase
    ) {
    }

    #[Route(route: '/event', name: 'event.create', methods: ['POST'])]
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            if (!isset($data['user_id'], $data['event_type'], $data['timestamp'])) {
                return $this->response->json([
                    'error' => 'Missing required fields: user_id, event_type, timestamp',
                ], 400);
            }

            $createRequest = new CreateEventRequest(
                userId: $data['user_id'],
                eventType: $data['event_type'],
                timestamp: new DateTimeImmutable($data['timestamp']),
                metadata: $data['metadata'] ?? []
            );

            $event = $this->createEventUseCase->execute($createRequest);

            return $this->response->json([
                'data' => $event,
            ], 201);
        } catch (Exception $e) {
            return $this->response->json([
                'error' => 'Invalid request data: ' . $e->getMessage(),
            ], 400);
        }
    }

    #[Route(route: '/events', name: 'events.create.batch', methods: ['POST'])]
    public function createBatch(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            if (empty($data) || !is_array($data)) {
                return $this->response->json([
                    'error' => 'Missing required fields: events',
                ], 400);
            }

            $createRequest = new CreateEventsRequest($data);

            $result = $this->createEventsUseCase->execute($createRequest);

            return $this->response->json([
                'data' => $result ? 'queued' : 'failed to queue',
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
        try {
            $query = $request->getQueryParams();

            $page = max(1, (int)($query['page'] ?? 1));
            $limit = max(1, (int)($query['limit'] ?? 1));

            $getRequest = new GetEventsRequest(
                page: $page,
                limit: $limit
            );

            $result = $this->getEventsUseCase->execute($getRequest);

            return $this->response->json([
                'data' => $result->events,
                'query' => [
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

    #[Route(route: '/events/total', name: 'events.total', methods: ['GET'])]
    public function total(): ResponseInterface
    {
        try {
            $result = $this->totalEventsUseCase->execute();

            return $this->response->json([
                'data' => $result->total,
            ]);
        } catch (Exception $e) {
            return $this->response->json([
                'error' => 'Failed to retrieve events count: ' . $e->getMessage(),
            ], 500);
        }
    }

    #[Route(route: '/events', name: 'event.delete', methods: ['DELETE'])]
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $query = $request->getQueryParams();
            if (!isset($query['before'])) {
                return $this->response->json([
                    'error' => 'Parameter "before" is required',
                ], 400);
            }

            $deleteRequest = new DeleteEventsRequest(
                before: new DateTimeImmutable($query['before'])
            );
            $deletedCount = $this->deleteEventsUseCase->execute($deleteRequest);

            return $this->response->json([
                'data' => [
                    'deleted_events' => $deletedCount,
                ],
            ]);
        } catch (Exception $e) {
            return $this->response->json([
                'error' => 'Invalid date format or deletion failed: ' . $e->getMessage(),
            ], 400);
        }
    }
}
