<?php

declare(strict_types=1);

namespace App\Infra\Swoole;

use App\Infra\Http\SwooleRequestFactory;
use App\Infra\Http\SwooleResponseEmitter;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\ScopeInterface;
use Spiral\Http\Http;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as HttpServer;
use Throwable;

final class Server
{
    private HttpServer $server;
    private array $swooleConfig;

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ConfiguratorInterface $config,
        private readonly EnvironmentInterface $env
    ) {
        $this->swooleConfig = $this->config->getConfig('swoole');
        $this->server = new HttpServer(
            $this->swooleConfig['host'] ?? '0.0.0.0',
            (int) ($this->swooleConfig['port'] ?? 9501),
            SWOOLE_PROCESS,
            SWOOLE_SOCK_TCP
        );

        $this->configure();
        $this->registerHandlers();
    }

    public function start(): void
    {
        $this->server->start();
    }

    public function stop(): void
    {
        $this->server->shutdown();
    }

    public function reload(): void
    {
        $this->server->reload();
    }

    public function status(): array
    {
        return [
            'master_pid' => $this->server->master_pid,
            'manager_pid' => $this->server->manager_pid,
            'worker_id' => $this->server->worker_id,
            'worker_pid' => $this->server->worker_pid,
            'stats' => $this->server->stats(),
        ];
    }

    private function configure(): void
    {
        $this->server->set([
            'worker_num' => $this->swooleConfig['worker_num'] ?? swoole_cpu_num() * 2,
            'task_worker_num' => $this->swooleConfig['task_worker_num'] ?? 4,
            'max_request' => $this->swooleConfig['max_request'] ?? 10000,
            'enable_coroutine' => true,
            'hook_flags' => SWOOLE_HOOK_ALL,
            'max_coroutine' => $this->swooleConfig['max_coroutine'] ?? 10000,
            'package_max_length' => $this->swooleConfig['package_max_length'] ?? 2 * 1024 * 1024,
            'buffer_output_size' => $this->swooleConfig['buffer_output_size'] ?? 2 * 1024 * 1024,
            'socket_buffer_size' => $this->swooleConfig['socket_buffer_size'] ?? 128 * 1024 * 1024,
            'log_file' => $this->swooleConfig['log_file'] ?? '/app/var/log/swoole.log',
            'log_level' => $this->swooleConfig['log_level'] ?? SWOOLE_LOG_INFO,
            'pid_file' => $this->swooleConfig['pid_file'] ?? '/app/var/swoole.pid',
            'enable_static_handler' => $this->swooleConfig['enable_static_handler'] ?? true,
            'document_root' => $this->swooleConfig['document_root'] ?? '/app/public',
            'static_handler_locations' => $this->swooleConfig['static_handler_locations'] ?? ['/'],
        ]);
    }

    private function registerHandlers(): void
    {
        $this->server->on('Start', [$this, 'onStart']);
        $this->server->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->server->on('Request', [$this, 'onRequest']);
        $this->server->on('Task', [$this, 'onTask']);
        $this->server->on('Finish', [$this, 'onFinish']);
        $this->server->on('WorkerStop', [$this, 'onWorkerStop']);
        $this->server->on('Shutdown', [$this, 'onShutdown']);
    }

    public function onStart(HttpServer $server): void
    {
        echo "HTTP Server started at {$this->swooleConfig['host']}:{$this->swooleConfig['port']}\n";
    }

    public function onWorkerStart(HttpServer $server, int $workerId): void
    {
        if ($server->taskworker) {
            echo "Task Worker #{$workerId} started\n";
        } else {
            echo "Worker #{$workerId} started\n";
        }
    }

    public function onRequest(Request $request, Response $response): void
    {
        try {
            $psrRequest = $this->createPsrRequest($request);
            $psrResponse = $this->handleRequest($psrRequest);
            $this->sendResponse($response, $psrResponse);
        } catch (Throwable $e) {
            $this->sendErrorResponse($response, $e);
        }
    }

    public function dispatchTask(array $data): void
    {
        $this->server->task($data);
    }

    public function onTask(HttpServer $server, int $taskId, int $srcWorkerId, mixed $data): bool
    {
        echo "Task #{$taskId} received from Worker #{$srcWorkerId}\n";

        $taskClass = $data['taskClass'] ?? null;
        if ($taskClass === null) {
            return false;
        }

        $task = $this->container->get($taskClass);
        $task->run($data);

        return true;
    }

    public function onFinish(HttpServer $server, int $taskId, mixed $data): void
    {
        echo "Task #{$taskId} finished\n";
    }

    public function onWorkerStop(HttpServer $server, int $workerId): void
    {
        echo "Worker #{$workerId} stopped\n";
    }

    public function onShutdown(HttpServer $server): void
    {
        echo "Server shutdown\n";
    }

    private function createPsrRequest(Request $swooleRequest): ServerRequestInterface
    {
        return SwooleRequestFactory::fromSwoole($swooleRequest);
    }

    private function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        return $this->container->get(ScopeInterface::class)->runScope(
            ['request' => $request],
            fn () => $this->container->get(Http::class)->handle($request)
        );
    }

    private function sendResponse(Response $swooleResponse, ResponseInterface $psrResponse): void
    {
        SwooleResponseEmitter::emit($swooleResponse, $psrResponse);
    }

    private function sendErrorResponse(Response $response, Throwable $e): void
    {
        $response->status(500);
        $response->header('Content-Type', 'application/json');

        $errorData = [
            'error' => $e->getMessage(),
            'type' => get_class($e),
        ];

        if ($this->env->get('DEBUG')) {
            $errorData['trace'] = array_map(static function ($trace) {
                return array_filter($trace, static function ($key) {
                    return !in_array($key, ['args', 'object']);
                }, ARRAY_FILTER_USE_KEY);
            }, $e->getTrace());
        }

        $response->end(json_encode($errorData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
