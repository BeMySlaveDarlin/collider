<?php

declare(strict_types=1);

namespace App\Infra\Metrics;

class MetricsCollector
{
    public function createMetrics(): Metrics
    {
        return new Metrics(
            microtime(true)
        );
    }
}
