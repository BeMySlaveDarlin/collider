<?php

declare(strict_types=1);

namespace App\Infrastructure\Generator;

use RuntimeException;
use Swoole\Atomic\Long;

class SnowflakeIdGenerator
{
    private const int EPOCH = 1640995200000;
    private const int WORKER_ID_BITS = 10;
    private const int SEQUENCE_BITS = 12;

    private const int MAX_WORKER_ID = (1 << self::WORKER_ID_BITS) - 1;
    private const int MAX_SEQUENCE = (1 << self::SEQUENCE_BITS) - 1;

    private const int WORKER_ID_SHIFT = self::SEQUENCE_BITS;
    private const int TIMESTAMP_LEFT_SHIFT = self::SEQUENCE_BITS + self::WORKER_ID_BITS;

    private static ?Long $counter = null;
    private int $workerId;

    public function __construct()
    {
        if (self::$counter === null) {
            self::$counter = new Long(0);
        }

        $this->workerId = $this->calculateWorkerId();
    }

    public function generate(): int
    {
        $maxAttempts = 100000;
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $timestamp = $this->timeGen();
            $timestampShifted = ($timestamp - self::EPOCH) << self::TIMESTAMP_LEFT_SHIFT;

            $sequence = self::$counter?->add() & self::MAX_SEQUENCE;

            if ($sequence == 0) {
                usleep(1000);
                $attempts++;
                continue;
            }

            return $timestampShifted | ($this->workerId << self::WORKER_ID_SHIFT) | $sequence;
        }

        throw new RuntimeException('Failed to generate ID after ' . $maxAttempts . ' attempts');
    }

    private function calculateWorkerId(): int
    {
        $pid = getmypid() ?: 1;
        $hostname = gethostname() ?: 'localhost';
        $hash = crc32($hostname . ':' . $pid);

        return abs($hash) % self::MAX_WORKER_ID;
    }

    protected function timeGen(): int
    {
        return intval(microtime(true) * 1000);
    }

    public function parseId(int $id): array
    {
        $timestamp = ($id >> self::TIMESTAMP_LEFT_SHIFT) + self::EPOCH;
        $workerId = ($id >> self::WORKER_ID_SHIFT) & self::MAX_WORKER_ID;
        $sequence = $id & self::MAX_SEQUENCE;

        return [
            'timestamp' => $timestamp,
            'datetime' => date('Y-m-d H:i:s', intval($timestamp / 1000)),
            'workerId' => $workerId,
            'sequence' => $sequence,
        ];
    }
}
