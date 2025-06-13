<?php

declare(strict_types=1);

namespace App\Infrastructure\Endpoint\Web;

use App\Application\Factory\CommandFactory;
use App\Application\Factory\QueryFactory;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractEndpoint
{
    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ResponseInterface $response;

    #[Inject]
    protected QueryFactory $queryFactory;

    #[Inject]
    protected CommandFactory $commandFactory;
}
