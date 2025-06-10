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
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Exception\BadRequestHttpException;
use Psr\Http\Message\ServerRequestInterface;

class EventController extends AbstractController
{
    #[Inject]
    protected CreateEventUseCase $createEventUseCase;
    #[Inject]
    protected CreateEventsUseCase $createEventsUseCase;
    #[Inject]
    protected GetEventsUseCase $getEventsUseCase;
    #[Inject]
    protected EventsTotalCountUseCase $totalEventsUseCase;
    #[Inject]
    protected DeleteEventsUseCase $deleteEventsUseCase;

    public function create(ServerRequestInterface $request): array
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            throw new BadRequestHttpException('Invalid data format');
        }
        if (!isset($data['user_id'], $data['event_type'], $data['timestamp'])) {
            throw new BadRequestHttpException('Please fill all the fields');
        }

        $createRequest = new CreateEventRequest(
            userId: $data['user_id'],
            eventType: $data['event_type'],
            timestamp: new DateTimeImmutable($data['timestamp']),
            metadata: $data['metadata'] ?? []
        );

        $event = $this->createEventUseCase->execute($createRequest);

        return [
            'data' => $event,
        ];
    }

    public function createBatch(ServerRequestInterface $request): array
    {
        $data = $request->getParsedBody();
        if (empty($data) || !is_array($data)) {
            throw  new BadRequestHttpException('Please fill all the fields');
        }

        $createRequest = new CreateEventsRequest($data);

        $result = $this->createEventsUseCase->execute($createRequest);

        return [
            'data' => $result ? 'queued' : 'failed to queue',
        ];
    }

    public function events(ServerRequestInterface $request): array
    {
        $query = $request->getQueryParams();

        $page = max(1, (int) ($query['page'] ?? 1));
        $limit = max(1, (int) ($query['limit'] ?? 1));

        $getRequest = new GetEventsRequest(
            page: $page,
            limit: $limit
        );

        $result = $this->getEventsUseCase->execute($getRequest);

        return [
            'data' => $result->events,
            'query' => [
                'page' => $result->page,
                'limit' => $result->limit,
                'total' => $result->total,
            ],
        ];
    }

    public function total(): array
    {
        $result = $this->totalEventsUseCase->execute();

        return [
            'data' => $result->total,
        ];
    }

    public function delete(ServerRequestInterface $request): array
    {
        $query = $request->getQueryParams();
        if (!isset($query['before'])) {
            throw new BadRequestHttpException('Parameter "before" is missing');
        }

        $deleteRequest = new DeleteEventsRequest(
            before: new DateTimeImmutable($query['before'])
        );
        $deletedCount = $this->deleteEventsUseCase->execute($deleteRequest);

        return [
            'data' => [
                'deleted_events' => $deletedCount,
            ],
        ];
    }
}
