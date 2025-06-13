<?php

declare(strict_types=1);

namespace App\Infrastructure\Endpoint\Web;

use Hyperf\Cache\Annotation\CacheEvict;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

#[Controller]
class IndexEndpoint extends AbstractEndpoint
{
    #[GetMapping('/')]
    public function index(): array
    {
        return [
            'data' => 'Ok',
        ];
    }

    #[CacheEvict(all: true)]
    #[GetMapping('/cache_clear')]
    public function clearCache(): array
    {
        return [
            'data' => 'Ok',
        ];
    }
}
