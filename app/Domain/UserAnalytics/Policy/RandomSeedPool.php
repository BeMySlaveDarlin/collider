<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Policy;

use RuntimeException;

class RandomSeedPool
{
    private const string CACHE_FILE = \BASE_PATH . '/runtime/caches/seed_pool.cache';

    private array $userIdPool = [];
    private int $userIdCount = 0;

    private array $typeNamePool = [];
    private int $typeNameCount = 0;

    private array $eventTypeIdPool = [];
    private int $eventTypeIdCount = 0;

    private array $metadataPool = [];
    private int $metadataCount = 0;

    private array $timestampPool = [];
    private int $timestampCount = 0;

    private int $userCounter = 0;
    private int $typeCounter = 0;
    private int $metadataCounter = 0;
    private int $timestampCounter = 0;

    public function __construct(
        protected SeedPolicy $seedPolicy,
        array $userIds,
        array $typeNameToId
    ) {
        if ($this->loadFromCache()) {
            return;
        }
        $typeNames = array_keys($typeNameToId);
        $types = $this->seedPolicy->getEventTypes();
        $referrers = $this->seedPolicy->getReferrers();
        $startTs = strtotime('-30 days');
        $endTs = time();

        $this->generateUserPools($userIds, $typeNames, $typeNameToId);
        $this->generateTimestampPool($startTs, $endTs);
        $this->generateMetadataPool($types, $referrers);

        $this->saveToCache();
    }

    private function generateUserPools(array $userIds, array $typeNames, array $typeNameToId): void
    {
        $userCount = count($userIds);
        $typeCount = count($typeNames);

        for ($i = 0; $i < 1000; $i++) {
            $userIndex = $i % $userCount;
            $typeIndex = $i % $typeCount;

            $typeName = $typeNames[$typeIndex];

            $this->userIdPool[] = $userIds[$userIndex];
            $this->typeNamePool[] = $typeName;
            $this->eventTypeIdPool[] = $typeNameToId[$typeName];
        }

        $this->userIdCount = 1000;
        $this->typeNameCount = 1000;
        $this->eventTypeIdCount = 1000;
    }

    private function generateMetadataPool(array $types, array $referrers): void
    {
        $typeNames = array_keys($types);
        $typeCount = count($typeNames);
        $refCount = count($referrers);

        for ($i = 0; $i < 100; $i++) {
            $typeIndex = $i % $typeCount;
            $refIndex = $i % $refCount;

            $typeName = $typeNames[$typeIndex];
            $page = $types[$typeName]['page'] ?? '/unknown';
            $referrer = $referrers[$refIndex];

            $this->metadataPool[] = $this->encodeJsonLight($page, $referrer);
        }

        $this->metadataCount = 100;
    }

    private function generateTimestampPool(int $startTs, int $endTs): void
    {
        $timeRange = $endTs - $startTs;
        $step = $timeRange / 1000;

        for ($i = 0; $i < 1000; $i++) {
            $ts = $startTs + (int) ($i * $step);
            $this->timestampPool[] = date('Y-m-d H:i:s', $ts);
        }

        $this->timestampCount = 1000;
    }

    public function getRandomUserId(): int
    {
        $userId = $this->userIdPool[$this->userCounter];
        $this->userCounter = ($this->userCounter + 1) % $this->userIdCount;

        return $userId;
    }

    public function getRandomEventTypeId(): int
    {
        $eventTypeId = $this->eventTypeIdPool[$this->typeCounter];
        $this->typeCounter = ($this->typeCounter + 1) % $this->eventTypeIdCount;

        return $eventTypeId;
    }

    public function getRandomMetadata(): string
    {
        $metadata = $this->metadataPool[$this->metadataCounter];
        $this->metadataCounter = ($this->metadataCounter + 1) % $this->metadataCount;

        return $metadata;
    }

    public function getRandomTimestamp(): string
    {
        $timestamp = $this->timestampPool[$this->timestampCounter];
        $this->timestampCounter = ($this->timestampCounter + 1) % $this->timestampCount;

        return $timestamp;
    }

    private function saveToCache(): void
    {
        $cacheDir = dirname(self::CACHE_FILE);
        if (!is_dir($cacheDir) && !mkdir($cacheDir, 0o755, true) && !is_dir($cacheDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $cacheDir));
        }

        $data = [
            'userIdPool' => $this->userIdPool,
            'userIdCount' => $this->userIdCount,
            'typeNamePool' => $this->typeNamePool,
            'typeNameCount' => $this->typeNameCount,
            'eventTypeIdPool' => $this->eventTypeIdPool,
            'eventTypeIdCount' => $this->eventTypeIdCount,
            'metadataPool' => $this->metadataPool,
            'metadataCount' => $this->metadataCount,
            'timestampPool' => $this->timestampPool,
            'timestampCount' => $this->timestampCount,
        ];

        file_put_contents(self::CACHE_FILE, serialize($data));
    }

    private function loadFromCache(): bool
    {
        if (!file_exists(self::CACHE_FILE)) {
            return false;
        }

        /** @var string $file */
        $file = file_get_contents(self::CACHE_FILE);

        /** @var array{
         *   userIdPool: array<int>,
         *   userIdCount: int,
         *   typeNamePool: array<string>,
         *   typeNameCount: int,
         *   eventTypeIdPool: array<int>,
         *   eventTypeIdCount: int,
         *   metadataPool: array<string>,
         *   metadataCount: int,
         *   timestampPool: array<string>,
         *   timestampCount: int
         * } $data
         */
        $data = unserialize($file);

        $this->userIdPool = $data['userIdPool'];
        $this->userIdCount = $data['userIdCount'];
        $this->typeNamePool = $data['typeNamePool'];
        $this->typeNameCount = $data['typeNameCount'];
        $this->eventTypeIdPool = $data['eventTypeIdPool'];
        $this->eventTypeIdCount = $data['eventTypeIdCount'];
        $this->metadataPool = $data['metadataPool'];
        $this->metadataCount = $data['metadataCount'];
        $this->timestampPool = $data['timestampPool'];
        $this->timestampCount = $data['timestampCount'];

        return true;
    }

    public static function clearCache(): void
    {
        if (file_exists(self::CACHE_FILE)) {
            unlink(self::CACHE_FILE);
        }
    }

    private function encodeJsonLight(string $page, string $referrer): string
    {
        $safePage = str_replace(['\\', '"'], ['\\\\', '\"'], $page);
        $safeRef = str_replace(['\\', '"'], ['\\\\', '\"'], $referrer);

        return '{"page":"' . $safePage . '","referrer":"' . $safeRef . '"}';
    }
}
