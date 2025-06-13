<?php

declare(strict_types=1);

namespace App\Infrastructure\Endpoint\Web;

use App\Application\Factory\QueryFactory;
use App\Application\UseCase\Stats\GetStatsUseCase;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ServerRequestInterface;

class StatsController extends AbstractController
{
    #[Inject]
    protected GetStatsUseCase $getStatsUseCase;
    #[Inject]
    protected QueryFactory $queryFactory;

    public function index(ServerRequestInterface $request): array
    {
        $query = $request->getQueryParams();
        $stats = $this->getStatsUseCase->execute(
            $this->queryFactory->getStatsQuery(
                $query
            )
        );

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
