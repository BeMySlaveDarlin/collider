<?php

declare(strict_types=1);

use App\Application\Application;
use Spiral\Boot\Environment;
use Spiral\Core\Container;
use Spiral\Core\Options;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/config/bootstrap.php';

const USE_SWOOLE = false;

$options = new Options();
$options->allowSingletonsRebinding = false;
$options->validateArguments = false;
$options->checkScope = false;
$container = new Container(options: $options);

$app = Application::create(
    directories: [
        'root' => ROOT_PATH,
        'app' => ROOT_PATH,
        'config' => ROOT_PATH . '/config',
        'cache' => ROOT_PATH . '/var/cache',
        'logs' => ROOT_PATH . '/var/log',
        'runtime' => ROOT_PATH . '/var',
    ],
    container: $container,
);

$env = new Environment(
    array_merge($_ENV, [
        'APP_ENV' => $_ENV['APP_ENV'] ?? 'dev',
        'DEBUG' => $_ENV['DEBUG'] ?? true,
    ])
);
$app->run($env);

$code = (int)$app->serve();
exit($code);
