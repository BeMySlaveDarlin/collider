<?php

declare(strict_types=1);

namespace App\Infrastructure\Endpoint\Web;

use App\Application\UseCase\Stats\GetStatsUseCase;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Psr\Http\Message\ServerRequestInterface;

#[Controller]
class StatsEndpoint extends AbstractEndpoint
{
    #[Inject]
    protected GetStatsUseCase $getStatsUseCase;

    #[GetMapping('/stats')]
    public function index(ServerRequestInterface $request): array
    {
        $query = $this->queryFactory->getStatsQuery($request->getQueryParams());

        $stats = $this->getStatsUseCase->execute($query);

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
