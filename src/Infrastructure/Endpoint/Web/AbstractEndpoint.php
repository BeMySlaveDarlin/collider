<?php

declare(strict_types=1);

namespace App\Infrastructure\Endpoint\Web;

use App\Application\Factory\CommandFactory;
use App\Application\Factory\QueryFactory;
use Hyperf\Di\Annotation\Inject;

abstract class AbstractEndpoint
{
    #[Inject]
    protected QueryFactory $queryFactory;

    #[Inject]
    protected CommandFactory $commandFactory;
}
