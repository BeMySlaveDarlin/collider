<?php

declare(strict_types=1);

namespace App\Endpoint\Web;

use App\Infra\Redis\RedisCacheService;
use Psr\Http\Message\ResponseInterface;
use Spiral\Http\ResponseWrapper;
use Spiral\Router\Annotation\Route;

final readonly class IndexController
{
    public function __construct(
        private ResponseWrapper $response,
        private RedisCacheService $cache
    ) {
    }

    #[Route(route: '/', name: 'home', methods: ['GET', 'POST'])]
    public function index(): ResponseInterface
    {
        return $this->response->json([
            'data' => 'Ok',
        ]);
    }

    #[Route(route: '/clear_cache', name: 'clear-cache', methods: ['GET', 'POST'])]
    public function clearCache(): ResponseInterface
    {
        $result = $this->cache->flush();

        return $this->response->json([
            'data' => $result ? 'Ok' : 'Fail',
        ]);
    }
}
