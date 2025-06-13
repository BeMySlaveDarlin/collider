<?php

declare(strict_types=1);

namespace App\Infrastructure\Exception\Handler;

use App\Application\Exception\DomainException;
use App\Application\Exception\InvalidArgumentException;
use App\Application\Exception\NotFoundException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    public function __construct(
        protected StdoutLoggerInterface $stdOutLogger,
        protected LoggerInterface $logger,
    ) {
    }

    public function handle(Throwable $throwable, ResponseInterface $response): MessageInterface | ResponseInterface
    {
        $this->stdOutLogger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->stdOutLogger->error($throwable->getTraceAsString());

        if ($throwable instanceof HttpException) {
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($throwable->getStatusCode())
                ->withBody(
                    new SwooleStream(
                        json_encode([
                            'code' => $throwable->getStatusCode(),
                            'message' => $throwable->getMessage(),
                        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                    )
                );
        }

        if ($throwable instanceof DomainException) {
            $code = $this->getDomainExceptionCode($throwable);

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($code)
                ->withBody(
                    new SwooleStream(
                        json_encode([
                            'code' => $code,
                            'message' => $throwable->getMessage(),
                        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                    )
                );
        }

        $this->logger->error($throwable->getMessage(), [
            'code' => $throwable->getCode(),
            'line' => $throwable->getLine(),
            'file' => $throwable->getFile(),
        ]);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Server', 'Hyperf')
            ->withStatus(500)
            ->withBody(
                new SwooleStream(
                    json_encode([
                        'code' => 500,
                        'message' => 'Internal Server Error',
                        'error' => $throwable->getMessage(),
                    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                )
            );
    }

    private function getDomainExceptionCode(DomainException $exception): int
    {
        switch (get_class($exception)) {
            default:
            case DomainException::class:
                return (int) $exception->getCode();
            case NotFoundException::class:
                return 404;
            case InvalidArgumentException::class:
                return 400;
        }
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
