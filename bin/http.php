<?php

declare(strict_types=1);

use App\Application\Application;
use App\Infra\Swoole\Server;
use Spiral\Boot\Environment;
use Spiral\Core\Container;
use Spiral\Core\Options;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/config/bootstrap.php';

const USE_SWOOLE = true;

$options = new Options();
$options->allowSingletonsRebinding = true;
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
    container: $container
);

$env = new Environment(
    array_merge($_ENV, [
        'APP_ENV' => $_ENV['APP_ENV'] ?? 'dev',
        'DEBUG' => $_ENV['DEBUG'] ?? true,
    ])
);
$app->run($env);

$server = $container->get(Server::class);
$command = $argv[1] ?? 'start';
switch ($command) {
    case 'start':
        $server->start();
        break;
    case 'stop':
        $server->stop();
        break;
    case 'reload':
        $server->reload();
        break;
    case 'status':
        print_r($server->status());
        break;
    default:
        echo "Usage: {$argv[0]} {start|stop|reload|status}\n";
        exit(1);
}
