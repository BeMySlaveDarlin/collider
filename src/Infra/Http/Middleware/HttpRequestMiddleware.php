<?php

declare(strict_types=1);

namespace App\Infra\Http\Middleware;

use App\Infra\Metrics\MetricsCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Http\ResponseWrapper;
use Spiral\Logger\Traits\LoggerTrait;

final class HttpRequestMiddleware implements MiddlewareInterface
{
    use LoggerTrait;

    public function __construct(
        private readonly MetricsCollector $collector,
        private readonly ResponseWrapper $response
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $metrics = $this->collector->createMetrics();

        $response = $handler->handle($request);

        $metrics->collect();

        $body = (string) $response->getBody();
        $json = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        $this->getLogger()->info(
            'HTTP Request',
            [
                'method' => $request->getMethod(),
                'path' => $request->getUri()->getPath(),
                'query' => $request->getUri()->getQuery(),
                'headers' => $request->getHeaders(),
                'body' => $body,
                'metrics' => $metrics,
            ]
        );

        if (is_array($json)) {
            return $this->response
                ->json($json)
                ->withStatus($response->getStatusCode());
        }

        return $response;
    }
}
