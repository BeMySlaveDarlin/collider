<?php

declare(strict_types=1);

namespace App\Endpoint\Web;

use App\Domain\UserAnalytics\UseCase\Stats\GetStatsUseCase;
use App\Domain\UserAnalytics\ValueObject\GetStatsRequest;
use DateTimeImmutable;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Http\ResponseWrapper;
use Spiral\Router\Annotation\Route;

final readonly class StatsController
{
    public function __construct(
        private ResponseWrapper $response,
        private GetStatsUseCase $getStatsUseCase
    ) {
    }

    #[Route(route: '/stats', name: 'stats', methods: ['GET'])]
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $query = $request->getQueryParams();
            $statsRequest = new GetStatsRequest(
                limit: isset($query['limit']) ? (int) $query['limit'] : null,
                from: isset($query['from']) ? new DateTimeImmutable($query['from']) : null,
                to: isset($query['to']) ? new DateTimeImmutable($query['to']) : null,
                type: $query['type'] ?? null
            );

            $stats = $this->getStatsUseCase->execute($statsRequest);

            return $this->response->json([
                'data' => [
                    'total_events' => $stats->totalEvents,
                    'unique_users' => $stats->uniqueUsers,
                    'top_pages' => $stats->topPages,
                ],
                'query' => $query,
            ]);
        } catch (Exception $e) {
            return $this->response->json([
                'error' => 'Failed to retrieve stats: ' . $e->getMessage(),
            ], 500);
        }
    }
}
