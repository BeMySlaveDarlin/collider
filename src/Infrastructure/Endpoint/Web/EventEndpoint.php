<?php

declare(strict_types=1);

namespace App\Infrastructure\Endpoint\Web;

use App\Application\UseCase\Event\CreateEventsUseCase;
use App\Application\UseCase\Event\CreateEventUseCase;
use App\Application\UseCase\Event\DeleteEventsUseCase;
use App\Application\UseCase\Event\EventsTotalCountUseCase;
use App\Application\UseCase\Event\GetEventsUseCase;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Psr\Http\Message\ServerRequestInterface;

#[Controller]
class EventEndpoint extends AbstractEndpoint
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

    #[PostMapping('/events')]
    public function create(ServerRequestInterface $request): array
    {
        /** @var array<string, mixed> $body */
        $body = $request->getParsedBody();
        $command = $this->commandFactory->createEventCommand($body);

        $event = $this->createEventUseCase->execute($command);

        return [
            'data' => $event,
        ];
    }

    #[PostMapping('/events/batch')]
    public function createBatch(ServerRequestInterface $request): array
    {
        /** @var array<string, mixed> $body */
        $body = $request->getParsedBody();
        $command = $this->commandFactory->createEventsCommand($body);

        $result = $this->createEventsUseCase->execute($command);

        return [
            'data' => $result ? 'queued' : 'failed to queue',
        ];
    }

    #[GetMapping('/events')]
    public function events(ServerRequestInterface $request): array
    {
        $query = $this->queryFactory->getEventsQuery($request->getQueryParams());

        $result = $this->getEventsUseCase->execute($query);

        return [
            'data' => $result->events,
            'query' => [
                'page' => $result->page,
                'limit' => $result->limit,
                'total' => $result->total,
            ],
        ];
    }

    #[GetMapping('/events/total')]
    public function total(): array
    {
        $result = $this->totalEventsUseCase->execute();

        return [
            'data' => $result->total,
        ];
    }

    #[DeleteMapping('/events')]
    public function delete(ServerRequestInterface $request): array
    {
        $query = $this->queryFactory->getDeleteEventsQuery($request->getQueryParams());

        $deletedCount = $this->deleteEventsUseCase->execute($query);

        return [
            'data' => [
                'deleted_events' => $deletedCount,
            ],
        ];
    }
}
