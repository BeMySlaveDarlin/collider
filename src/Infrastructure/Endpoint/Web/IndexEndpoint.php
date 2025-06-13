<?php

declare(strict_types=1);

namespace App\Infrastructure\Endpoint\Web;

use Hyperf\Cache\Annotation\CacheEvict;

class IndexEndpoint extends AbstractEndpoint
{
    public function index(): array
    {
        return [
            'data' => 'Ok',
        ];
    }

    #[CacheEvict(all: true)]
    public function clearCache(): array
    {
        return [
            'data' => 'Ok',
        ];
    }
}
