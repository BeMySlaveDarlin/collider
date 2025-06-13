<?php

declare(strict_types=1);

namespace App\Application\Dto;

class BatchParams
{
    public function __construct(
        public int $batch = 0,
        public int | float $batches = 0,
        public int $batchSize = 0
    ) {
    }
}
