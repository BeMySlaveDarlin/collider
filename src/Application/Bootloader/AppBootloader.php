<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use ErrorException;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Bootloader\DomainBootloader;
use Spiral\Core\Container;
use Spiral\Cycle\Interceptor\CycleInterceptor;
use Spiral\Domain\GuardInterceptor;
use Spiral\Interceptors\HandlerInterface;
use Swoole\Runtime;

use function extension_loaded;

final class AppBootloader extends DomainBootloader
{
    protected const array SINGLETONS = [HandlerInterface::class => [self::class, 'domainCore']];

    protected const array INTERCEPTORS = [
        CycleInterceptor::class,
        GuardInterceptor::class,
    ];

    public function defineDependencies(): array
    {
        return [
            ConsoleBootloader::class,
        ];
    }

    public function defineSingletons(): array
    {
        return [];
    }

    /**
     * @throws ErrorException
     */
    public function init(Container $container, EnvironmentInterface $env): void
    {
        if (!extension_loaded('swoole')) {
            throw new ErrorException('Swoole extension is required but not loaded');
        }

        if (USE_SWOOLE) {
            Runtime::enableCoroutine();
        }
    }
}
