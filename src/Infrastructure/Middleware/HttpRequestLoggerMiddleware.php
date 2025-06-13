<?php

declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class HttpRequestLoggerMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected LoggerFactory $loggerFactory,
        protected RequestInterface $request,
        protected ResponseInterface $response
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): PsrResponseInterface
    {
        $logger = $this->loggerFactory->get('request');

        $uri = (string) $request->getUri();
        $method = $request->getMethod();
        $query = $request->getQueryParams();
        $body = $request->getParsedBody();

        try {
            $startTime = microtime(true);
            $startMemory = memory_get_usage(true);

            $response = $handler->handle($request);
            $status = $response->getStatusCode();

            $durationMs = round((microtime(true) - $startTime) * 1000, 2);
            $memoryUsed = memory_get_usage(true) - $startMemory;
            $peakMemory = memory_get_peak_usage(true);

            $logger->info(
                \sprintf(
                    'HTTP Request %s %s',
                    $method,
                    $uri
                ),
                [
                    'query' => $query,
                    'body' => $body,
                    'metrics' => [
                        'status' => $status,
                        'duration' => $durationMs . 'ms',
                        'memory' => \sprintf(
                            'memory=%.2f KB, peak=%.2f KB',
                            $memoryUsed / 1024,
                            $peakMemory / 1024
                        ),
                    ],
                ]
            );
        } catch (Throwable $e) {
            $logger->error(
                \sprintf(
                    'HTTP Request %s %s',
                    $method,
                    $uri,
                ),
                [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'query' => $query,
                    'body' => $body,
                ]
            );

            throw $e;
        }

        return $response;
    }
}
