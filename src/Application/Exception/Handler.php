<?php

declare(strict_types=1);

namespace App\Application\Exception;

use ErrorException;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Spiral\Exceptions\ExceptionHandlerInterface;
use Spiral\Exceptions\ExceptionRendererInterface;
use Spiral\Exceptions\Verbosity;
use Spiral\Http\Exception\HttpException;
use Throwable;

final class Handler implements ExceptionHandlerInterface
{
    private array $renderers = [];
    private bool $registered = false;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly bool $debug = false
    ) {
    }

    public function handle(Throwable $exception): void
    {
        $context = [
            'exception' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'class' => get_class($exception),
        ];

        if ($this->debug) {
            $context['trace'] = $exception->getTraceAsString();
        }

        if ($exception->getPrevious()) {
            $context['previous'] = [
                'message' => $exception->getPrevious()->getMessage(),
                'class' => get_class($exception->getPrevious()),
            ];
        }

        $this->logger->error('Unhandled exception', $context);
    }

    public function renderException(ServerRequestInterface $request, Throwable $exception): ResponseInterface
    {
        $this->logExceptionWithContext($exception, $request);

        if ($exception instanceof HttpException) {
            $statusCode = $this->validateHttpStatusCode($exception->getCode());

            return $this->createJsonResponse($statusCode, [
                'error' => $exception->getMessage(),
                'code' => $statusCode,
            ]);
        }

        return $this->createJsonResponse(500, [
            'error' => 'Internal Server Error',
            'code' => 500,
        ]);
    }

    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleGlobalException']);
        register_shutdown_function([$this, 'handleShutdown']);

        $this->registered = true;
    }

    public function handleGlobalException(Throwable $e): void
    {
        try {
            $this->handle($e);

            if (PHP_SAPI === 'cli') {
                fwrite(
                    STDERR,
                    sprintf(
                        "Fatal error: %s in %s:%d\n",
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine()
                    )
                );

                if ($this->debug) {
                    fwrite(STDERR, $e->getTraceAsString() . "\n");
                }
            } elseif (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json');

                echo json_encode([
                    'error' => $this->debug ? $e->getMessage() : 'Internal Server Error',
                    'code' => 500,
                ], JSON_THROW_ON_ERROR);
            }
        } catch (Throwable $handlerException) {
            $this->logger->error(
                sprintf(
                    'Exception handler failed: %s. Original exception: %s',
                    $handlerException->getMessage(),
                    $e->getMessage()
                ),
                ['exception' => $handlerException->getMessage()]
            );
        }
    }

    public function getRenderer(?string $format = null): ?ExceptionRendererInterface
    {
        $format ??= 'json';

        return $this->renderers[$format] ?? null;
    }

    public function render(Throwable $exception, ?Verbosity $verbosity = Verbosity::BASIC, ?string $format = null): string
    {
        $format ??= 'json';
        $verbosity ??= Verbosity::BASIC;

        $renderer = $this->getRenderer($format);
        if ($renderer !== null) {
            return $renderer->render($exception, $verbosity);
        }

        return $this->renderAsJson($exception, $verbosity);
    }

    public function canRender(string $format): bool
    {
        return isset($this->renderers[$format]) || $format === 'json';
    }

    public function report(Throwable $exception): void
    {
        try {
            $this->handle($exception);
        } catch (Throwable $reportingException) {
            $this->logger->error(
                sprintf(
                    'Failed to report exception: %s. Original exception: %s',
                    $reportingException->getMessage(),
                    $exception->getMessage()
                ),
                ['exception' => $reportingException->getMessage()]
            );
        }
    }

    public function addRenderer(string $format, ExceptionRendererInterface $renderer): void
    {
        $this->renderers[$format] = $renderer;
    }

    public function handleError(int $severity, string $message, string $file = '', int $line = 0): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    public function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $exception = new ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            );

            $this->handleGlobalException($exception);
        }
    }

    private function renderAsJson(Throwable $exception, Verbosity $verbosity): string
    {
        $data = [
            'error' => $exception->getMessage(),
            'class' => get_class($exception),
        ];

        if ($verbosity === Verbosity::VERBOSE || $this->debug) {
            $data['file'] = $exception->getFile();
            $data['line'] = $exception->getLine();
            $data['trace'] = $exception->getTraceAsString();
        }

        if ($exception instanceof HttpException) {
            $data['code'] = $this->validateHttpStatusCode($exception->getCode());
        } else {
            $data['code'] = 500;
        }

        return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function logExceptionWithContext(Throwable $exception, ServerRequestInterface $request): void
    {
        $context = [
            'exception' => $exception->getMessage(),
            'class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'request_method' => $request->getMethod(),
            'request_uri' => (string) $request->getUri(),
            'user_agent' => $request->getHeaderLine('User-Agent'),
        ];

        if ($this->debug) {
            $context['trace'] = $exception->getTraceAsString();
        }

        $this->logger->error('HTTP exception occurred', $context);
    }

    private function validateHttpStatusCode(int $code): int
    {
        if ($code < 100 || $code > 599) {
            return 500;
        }

        return $code;
    }

    private function createJsonResponse(int $statusCode, array $data): ResponseInterface
    {
        $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            $json = json_encode([
                'error' => 'Unable to encode response',
                'code' => 500,
            ], JSON_THROW_ON_ERROR);
            $statusCode = 500;
        }

        return new Response(
            $statusCode,
            [
                'Content-Type' => 'application/json',
                'Content-Length' => (string) strlen($json),
            ],
            $json
        );
    }
}
