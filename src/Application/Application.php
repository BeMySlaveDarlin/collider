<?php

declare(strict_types=1);

namespace App\Application;

use App\Application\Bootloader\AppBootloader;
use App\Application\Bootloader\ExceptionHandlerBootloader;
use App\Application\Bootloader\LoggingBootloader;
use App\Application\Bootloader\RoutesBootloader;
use App\Application\Bootloader\ServerBootloader;
use App\Application\Bootloader\SwooleBootloader;
use App\Application\Bootloader\TasksBootloader;
use Override;
use Spiral\Boot\Bootloader\CoreBootloader;
use Spiral\Bootloader as Framework;
use Spiral\Bootloader\Http\HttpBootloader;
use Spiral\Cache\Bootloader\CacheBootloader;
use Spiral\Cycle\Bootloader as CycleBridge;
use Spiral\DotEnv\Bootloader\DotenvBootloader;
use Spiral\Events\Bootloader\EventsBootloader;
use Spiral\Framework\Kernel;
use Spiral\League\Event\Bootloader\EventBootloader;
use Spiral\Monolog\Bootloader\MonologBootloader;
use Spiral\Nyholm\Bootloader\NyholmBootloader;
use Spiral\Prototype\Bootloader\PrototypeBootloader;
use Spiral\Scaffolder\Bootloader\ScaffolderBootloader;
use Spiral\Serializer\Bootloader\SerializerBootloader;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;
use Spiral\Validation\Bootloader\ValidationBootloader;
use Spiral\Validator\Bootloader\ValidatorBootloader;

final class Application extends Kernel
{
    #[Override]
    public function defineAppBootloaders(): array
    {
        return [
            AppBootloader::class,
            ServerBootloader::class,
            SwooleBootloader::class,
            TasksBootloader::class,
        ];
    }

    #[Override]
    public function defineSystemBootloaders(): array
    {
        return [
            CoreBootloader::class,
            DotenvBootloader::class,
            TokenizerListenerBootloader::class,
        ];
    }

    #[Override]
    public function defineBootloaders(): array
    {
        return [
            MonologBootloader::class,
            LoggingBootloader::class,
            ExceptionHandlerBootloader::class,

            Framework\SnapshotsBootloader::class,

            Framework\Security\EncrypterBootloader::class,
            Framework\Security\FiltersBootloader::class,
            Framework\Security\GuardBootloader::class,

            HttpBootloader::class,
            RoutesBootloader::class,
            Framework\Http\RouterBootloader::class,
            Framework\Http\JsonPayloadsBootloader::class,
            Framework\Http\CookiesBootloader::class,

            NyholmBootloader::class,
            CacheBootloader::class,
            EventsBootloader::class,
            EventBootloader::class,

            TokenizerListenerBootloader::class,
            PrototypeBootloader::class,
            ScaffolderBootloader::class,

            ValidationBootloader::class,
            ValidatorBootloader::class,
            SerializerBootloader::class,

            CycleBridge\DatabaseBootloader::class,
            CycleBridge\MigrationsBootloader::class,
            CycleBridge\DisconnectsBootloader::class,
            CycleBridge\SchemaBootloader::class,
            CycleBridge\CycleOrmBootloader::class,
            CycleBridge\ScaffolderBootloader::class,
            CycleBridge\AnnotatedBootloader::class,
            CycleBridge\CommandBootloader::class,
        ];
    }
}
