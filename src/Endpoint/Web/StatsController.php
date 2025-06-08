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
        $query = $request->getQueryParams();

        try {
            $from = null;
            $to = null;
            $limit = 3;

            if (isset($query['from'])) {
                $from = new DateTimeImmutable($query['from']);
            }

            if (isset($query['to'])) {
                $to = new DateTimeImmutable($query['to']);
            }

            if (isset($query['limit'])) {
                $limit = (int) $query['limit'];
            }

            if ($from && $to && $from > $to) {
                return $this->response->json([
                    'error' => 'Invalid date range: "from" date cannot be later than "to" date',
                ], 400);
            }

            $statsRequest = new GetStatsRequest(
                limit: $limit,
                from: $from,
                to: $to,
                type: $query['type'] ?? null
            );

            $stats = $this->getStatsUseCase->execute($statsRequest);

            return $this->response->json([
                'total_events' => $stats->totalEvents,
                'unique_users' => $stats->uniqueUsers,
                'top_pages' => $stats->topPages,
            ]);
        } catch (Exception $e) {
            return $this->response->json([
                'error' => 'Failed to retrieve stats: ' . $e->getMessage(),
            ], 500);
        }
    }
}
