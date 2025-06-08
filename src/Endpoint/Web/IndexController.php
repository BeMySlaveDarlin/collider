<?php

declare(strict_types=1);

namespace App\Endpoint\Web;

use Psr\Http\Message\ResponseInterface;
use Spiral\Http\ResponseWrapper;
use Spiral\Router\Annotation\Route;

final readonly class IndexController
{
    public function __construct(
        private ResponseWrapper $response,
    ) {
    }

    #[Route(route: '/', name: 'home', methods: ['GET', 'POST'])]
    public function index(): ResponseInterface
    {
        return $this->response->json([
            'message' => 'Ok',
        ]);
    }
}
