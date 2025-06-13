<?php

declare(strict_types=1);

namespace App\Infrastructure\Endpoint\Web;

use App\Application\Factory\CommandFactory;
use App\Application\Factory\QueryFactory;
use App\Application\UseCase\Event\CreateEventsUseCase;
use App\Application\UseCase\Event\CreateEventUseCase;
use App\Application\UseCase\Event\DeleteEventsUseCase;
use App\Application\UseCase\Event\EventsTotalCountUseCase;
use App\Application\UseCase\Event\GetEventsUseCase;
use Hyperf\Di\Annotation\Inject;
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
    #[Inject]
    protected QueryFactory $queryFactory;
    protected CommandFactory $commandFactory;

    public function create(ServerRequestInterface $request): array
    {
        /** @var array<string, mixed> $body */
        $body = $request->getParsedBody();
        $event = $this->createEventUseCase->execute(
            $this->commandFactory->createEventCommand(
                $body
            )
        );

        return [
            'data' => $event,
        ];
    }

    public function createBatch(ServerRequestInterface $request): array
    {
        /** @var array<string, mixed> $body */
        $body = $request->getParsedBody();
        $result = $this->createEventsUseCase->execute(
            $this->commandFactory->createEventsCommand(
                $body
            )
        );

        return [
            'data' => $result ? 'queued' : 'failed to queue',
        ];
    }

    public function events(ServerRequestInterface $request): array
    {
        $query = $request->getQueryParams();
        $result = $this->getEventsUseCase->execute(
            $this->queryFactory->getEventsQuery(
                $query
            )
        );

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
        $deletedCount = $this->deleteEventsUseCase->execute(
            $this->queryFactory->getDeleteEventsQuery(
                $query
            )
        );

        return [
            'data' => [
                'deleted_events' => $deletedCount,
            ],
        ];
    }
}
