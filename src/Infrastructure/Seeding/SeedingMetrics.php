<?php

declare(strict_types=1);

namespace App\Infrastructure\Seeding;

use Carbon\Carbon;

final class SeedingMetrics
{
    public ?Carbon $startTime = null;
    public ?Carbon $endTime = null;
    public int $startMemory = 0;
    public int $endMemory = 0;
    public int $peakMemory = 0;

    public function __construct()
    {
    }

    public function init(): void
    {
        $this->startTime = Carbon::now();
        $this->startMemory = memory_get_usage(true);
    }

    public function collect(): void
    {
        $this->endTime = Carbon::now();
        $this->endMemory = memory_get_usage(true);
        $this->peakMemory = memory_get_peak_usage(true);
    }

    public function getDuration(): float
    {
        return ($this->endTime?->getTimestampMs() - $this->startTime?->getTimestampMs()) / 1000;
    }

    public function getUsedMemory(): float
    {
        return $this->endMemory - $this->startMemory;
    }

    public function getUsedMemoryMb(): float
    {
        return $this->getUsedMemory() / 1024 / 1024;
    }

    public function getPeakMemoryMb(): float
    {
        return $this->peakMemory / 1024 / 1024;
    }

    public function getFormattedStartTime(): string
    {
        return $this->startTime?->format('Y-m-d H:i:s.u') ?? \date('Y-m-d H:i:s.u');
    }

    public function getFormattedEndTime(): string
    {
        return $this->endTime?->format('Y-m-d H:i:s.u') ?? \date('Y-m-d H:i:s.u');
    }
}
