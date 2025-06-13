<?php

declare(strict_types=1);

namespace App\Infrastructure\Server;

use Hyperf\Cache\Cache;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Engine\Http\Stream;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpServer\Server;
use Hyperf\Support\SafeCaller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Response;
use Throwable;

class CachedServer extends Server
{
    #[Inject]
    protected Cache $cache;

    public function onRequest($request, $response): void
    {
        try {
            CoordinatorManager::until(Constants::WORKER_START)->yield();

            [$psr7Request, $psr7Response] = $this->initRequestAndResponse($request, $response);
            $psr7Request = $this->coreMiddleware?->dispatch($psr7Request);

            /** @var ServerRequestInterface $psr7Request */
            $psr7Response = $this->handleWithHttpCache($psr7Request, $psr7Response, function (ServerRequestInterface $request) {
                return $this->dispatcher->dispatch($request, [], $this->coreMiddleware);
            });
        } catch (Throwable $throwable) {
            /** @var SafeCaller $caller */
            $caller = $this->container->get(SafeCaller::class);
            $psr7Response = $caller->call(function () use ($throwable) {
                return $this->exceptionHandlerDispatcher->dispatch($throwable, $this->exceptionHandlers);
            }, static function () {
                return (new Psr7Response())->withStatus(400);
            });
        } finally {
            if (!isset($psr7Response) || !$psr7Response instanceof ResponseInterface) {
                $psr7Response = (new Psr7Response())->withStatus(418);
            }
            /** @var Response $response */
            if (isset($psr7Request) && $psr7Request->getMethod() === 'HEAD') {
                $this->responseEmitter->emit($psr7Response, $response, false);
            } else {
                $this->responseEmitter->emit($psr7Response, $response);
            }
        }
    }

    private function handleWithHttpCache(ServerRequestInterface $request, ResponseInterface $emptyResponse, callable $resolver): ResponseInterface
    {
        if ($request->getMethod() !== 'GET') {
            return $resolver($request);
        }

        $uri = (string) $request->getUri();
        $attributes = $request->getAttributes();
        $cacheKey = 'events:' . md5($uri . ':' . serialize($attributes));

        if ($this->cache->has($cacheKey)) {
            /** @var string $body */
            $body = $this->cache->get($cacheKey);

            return $emptyResponse->withBody(new Stream($body));
        }

        $response = $resolver($request);

        if ($response->getStatusCode() === 200) {
            $this->cache->set($cacheKey, $response->getBody()->getContents(), 180);
        }

        return $response;
    }
}
