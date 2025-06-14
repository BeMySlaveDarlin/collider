<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Policy;

class RandomSeedPool
{
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

    public function __construct(
        array $userIds,
        array $typeNameToId,
        array $typeNames,
        array $types,
        array $referrers,
        int $startTs,
        int $endTs,
        int $count = 1000
    ) {
        $this->generateUserPools($userIds, $typeNames, $typeNameToId, $count);
        $this->generateMetadataPool($types, $referrers, $count);
        $this->generateTimestampPool($startTs, $endTs, $count);
    }

    private function generateUserPools(array $userIds, array $typeNames, array $typeNameToId, int $count): void
    {
        $userCount = count($userIds);
        $typeCount = count($typeNames);

        for ($i = 0; $i < $count; $i++) {
            $typeName = $typeNames[random_int(0, $typeCount - 1)];

            $this->userIdPool[] = $userIds[random_int(0, $userCount - 1)];
            $this->typeNamePool[] = $typeName;
            $this->eventTypeIdPool[] = $typeNameToId[$typeName];
        }

        $this->userIdCount = $count;
        $this->typeNameCount = $count;
        $this->eventTypeIdCount = $count;
    }

    private function generateMetadataPool(array $types, array $referrers, int $count): void
    {
        $typeNames = array_keys($types);
        $typeCount = count($typeNames);
        $refCount = count($referrers);

        for ($i = 0; $i < $count; $i++) {
            $typeName = $typeNames[random_int(0, $typeCount - 1)];
            $page = $types[$typeName]['page'] ?? '/unknown';
            $referrer = $referrers[random_int(0, $refCount - 1)];

            $this->metadataPool[] = json_encode([
                'page' => $page,
                'referrer' => $referrer,
            ], JSON_THROW_ON_ERROR);
        }

        $this->metadataCount = $count;
    }

    private function generateTimestampPool(int $startTs, int $endTs, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $randTs = random_int($startTs, $endTs);
            $this->timestampPool[] = date('Y-m-d H:i:s', $randTs);
        }

        $this->timestampCount = $count;
    }

    public function getRandomUserId(): int
    {
        return $this->userIdPool[random_int(0, $this->userIdCount - 1)];
    }

    public function getRandomTypeName(): string
    {
        return $this->typeNamePool[random_int(0, $this->typeNameCount - 1)];
    }

    public function getRandomEventTypeId(): int
    {
        return $this->eventTypeIdPool[random_int(0, $this->eventTypeIdCount - 1)];
    }

    public function getRandomMetadata(): string
    {
        return $this->metadataPool[random_int(0, $this->metadataCount - 1)];
    }

    public function getRandomTimestamp(): string
    {
        return $this->timestampPool[random_int(0, $this->timestampCount - 1)];
    }
}
