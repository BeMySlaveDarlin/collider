<?php

declare(strict_types=1);

namespace App\Endpoint\Web;

use App\Domain\UserAnalytics\UseCase\Stats\GetStatsUseCase;
use App\Domain\UserAnalytics\ValueObject\GetStatsRequest;
use DateTimeImmutable;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ServerRequestInterface;

class StatsController extends AbstractController
{
    #[Inject]
    protected GetStatsUseCase $getStatsUseCase;

    public function index(ServerRequestInterface $request): array
    {
        $query = $request->getQueryParams();
        $statsRequest = new GetStatsRequest(
            limit: isset($query['limit']) ? (int) $query['limit'] : 3,
            from: isset($query['from']) ? new DateTimeImmutable($query['from']) : null,
            to: isset($query['to']) ? new DateTimeImmutable($query['to']) : null,
            type: $query['type'] ?? null
        );

        $stats = $this->getStatsUseCase->execute($statsRequest);

        return [
            'data' => [
                'total_events' => $stats->totalEvents,
                'unique_users' => $stats->uniqueUsers,
                'top_pages' => $stats->topPages,
            ],
            'query' => $query,
        ];
    }
}
