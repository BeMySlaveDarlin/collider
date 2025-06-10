<?php

declare(strict_types=1);

namespace App\Infra\Metrics;

use JsonSerializable;

class Metrics implements JsonSerializable
{
    private float $startTime;
    private float $endTime = 0;
    private int $memory = 0;

    public function __construct(float $startTime)
    {
        $this->startTime = $startTime;
    }

    public function collect(): void
    {
        $this->endTime = microtime(true);
        $this->memory = memory_get_peak_usage();
    }

    public function jsonSerialize(): array
    {
        return [
            'startTime' => round($this->startTime, 4),
            'endTime' => round($this->endTime, 4),
            'duration' => round(($this->endTime - $this->startTime) * 1000, 3) . ' ms',
            'memory' => round($this->memory / 1024, 3) . ' kB',
        ];
    }
}
