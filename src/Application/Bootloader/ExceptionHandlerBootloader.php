<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\Environment\AppEnvironment;
use Spiral\Exceptions\ExceptionHandler;
use Spiral\Exceptions\Renderer\ConsoleRenderer;
use Spiral\Exceptions\Renderer\JsonRenderer;
use Spiral\Exceptions\Reporter\FileReporter;
use Spiral\Exceptions\Reporter\LoggerReporter;
use Spiral\Http\ErrorHandler\PlainRenderer;
use Spiral\Http\ErrorHandler\RendererInterface;
use Spiral\Http\Middleware\ErrorHandlerMiddleware\EnvSuppressErrors;
use Spiral\Http\Middleware\ErrorHandlerMiddleware\SuppressErrorsInterface;

final class ExceptionHandlerBootloader extends Bootloader
{
    protected const array BINDINGS = [
        SuppressErrorsInterface::class => EnvSuppressErrors::class,
        RendererInterface::class => PlainRenderer::class,
    ];

    public function __construct(
        private readonly ExceptionHandler $handler,
    ) {
    }

    public function init(AbstractKernel $kernel): void
    {
        $this->handler->addRenderer(new ConsoleRenderer());

        $kernel->running(function (): void {
            $this->handler->addRenderer(new JsonRenderer());
        });
    }

    public function boot(LoggerReporter $logger, FileReporter $files, AppEnvironment $appEnv): void
    {
        $this->handler->addReporter($logger);
        if ($appEnv->isLocal()) {
            $this->handler->addReporter($files);
        }
    }
}
