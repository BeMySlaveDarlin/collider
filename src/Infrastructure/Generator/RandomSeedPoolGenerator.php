<?php

declare(strict_types=1);

namespace App\Infrastructure\Generator;

use App\Infrastructure\Config\EventsConfig;
use Ds\Vector;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use RuntimeException;

class RandomSeedPoolGenerator
{
    private const string CACHE_FILE = \BASE_PATH . '/runtime/caches/seed_pool.cache';

    /** @var Vector<string> */
    private Vector $readyStringsPool;
    private int $poolSize = 0;
    private int $counter = 0;

    #[Inject]
    protected Redis $redis;

    public function __construct(
        protected SnowflakeIdGenerator $idGenerator,
        protected EventsConfig $eventsConfig,
        array $userIds,
        array $typeNameToId
    ) {
        $this->readyStringsPool = new Vector();

        if ($this->loadFromCache()) {
            return;
        }

        $typeNames = array_keys($typeNameToId);
        $startTs = strtotime('-30 days');
        $endTs = time();

        $this->generateReadyStrings($userIds, $typeNames, $typeNameToId, $startTs, $endTs);
        $this->saveToCache();
    }

    private function generateReadyStrings(array $userIds, array $typeNames, array $typeNameToId, int $startTs, int $endTs): void
    {
        $userCount = count($userIds);
        $typeCount = count($typeNames);
        $poolSize = 1000;

        $timeRange = $endTs - $startTs;
        $step = $timeRange / $poolSize;

        $eventTypes = $this->eventsConfig->getEventTypes();
        $eventTypeNames = array_keys($eventTypes);
        $eventTypeCount = count($eventTypeNames);

        for ($i = 0; $i < $poolSize; $i++) {
            $userIndex = $i % $userCount;
            $typeIndex = $i % $typeCount;
            $typeName = $typeNames[$typeIndex];

            $userId = $userIds[$userIndex];
            $eventTypeId = $typeNameToId[$typeName];

            $ts = $startTs + (int) ($i * $step);
            $timestamp = date('Y-m-d H:i:s', $ts);

            $metaTypeIndex = $i % $eventTypeCount;
            $metaTypeName = $eventTypeNames[$metaTypeIndex];
            $page = $eventTypes[$metaTypeName]['page'] ?? '/unknown';
            $metadata = json_encode(['page' => $page]);

            $readyString = "(#id#,$userId,$eventTypeId,'$timestamp','$metadata')";

            $this->readyStringsPool->push($readyString);
        }

        $this->poolSize = $poolSize;
    }

    public function getValues(int $batchSize): array
    {
        $batch = [];
        $localCounter = $this->counter;
        for ($i = 0; $i < $batchSize; ++$i) {
            $index = $localCounter % $this->poolSize;
            $value = $this->readyStringsPool->get($index);
            $batch[] = str_replace('#id#', (string) $this->idGenerator->generate(), $value);
            ++$localCounter;
        }

        return $batch;
    }

    private function saveToCache(): void
    {
        $cacheDir = dirname(self::CACHE_FILE);
        if (!is_dir($cacheDir) && !mkdir($cacheDir, 0o755, true) && !is_dir($cacheDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $cacheDir));
        }

        $data = [
            'readyStringsPool' => $this->readyStringsPool->toArray(),
            'poolSize' => $this->poolSize,
        ];

        file_put_contents(self::CACHE_FILE, serialize($data), LOCK_EX);
    }

    private function loadFromCache(): bool
    {
        if (!file_exists(self::CACHE_FILE)) {
            return false;
        }

        $file = file_get_contents(self::CACHE_FILE);
        if (!is_string($file)) {
            return false;
        }

        /** @var array{
         *   readyStringsPool: array<int, string>|null,
         *   poolSize: int|null,
         * } $data
         */
        $data = unserialize($file);

        if (!is_array($data) || !isset($data['readyStringsPool'], $data['poolSize'])) {
            return false;
        }

        $this->readyStringsPool = new Vector($data['readyStringsPool']);
        $this->poolSize = $data['poolSize'];

        return true;
    }
}
