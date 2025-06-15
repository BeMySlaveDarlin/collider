<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\ValueObject;

class BatchParamsDto
{
    public function __construct(
        public int $batch = 0,
        public int | float $batches = 0,
        public int $batchSize = 0
    ) {
    }
}
