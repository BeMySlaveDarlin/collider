<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\Event;

use App\Domain\UserAnalytics\Policy\SeedPolicy;
use Cycle\Database\DatabaseInterface;
use Faker\Factory;
use Faker\Generator;
use PDO;

final readonly class SeedDatabaseUseCase
{
    private Generator $faker;

    public function __construct(
        private DatabaseInterface $database,
        private SeedPolicy $seedPolicy
    ) {
        $this->faker = Factory::create();
    }

    public function execute(callable $logger): void
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $this->cleanDatabase($logger);
        $this->seedUsers($logger);
        $this->seedEventTypes($logger);
        $this->seedEvents($logger);

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);

        $duration = $endTime - $startTime;
        $usedMemory = $endMemory - $startMemory;

        $logger(sprintf('Seeding completed in %.2f seconds.', $duration));
        $logger(
            sprintf(
                'Memory used: %.2f MB (peak: %.2f MB)',
                $usedMemory / 1024 / 1024,
                $peakMemory / 1024 / 1024
            )
        );
    }

    private function cleanDatabase(callable $logger): void
    {
        $logger('Cleaning database...');

        $this->database->execute('SET session_replication_role = replica');

        $this->database->execute('TRUNCATE TABLE events RESTART IDENTITY CASCADE');
        $this->database->execute('TRUNCATE TABLE event_types RESTART IDENTITY CASCADE');
        $this->database->execute('TRUNCATE TABLE users RESTART IDENTITY CASCADE');

        $this->database->execute('SET session_replication_role = DEFAULT');

        $logger('Database cleaned.');
    }

    private function seedUsers(callable $logger): void
    {
        $logger('Seeding users...');

        $values = [];

        for ($i = 1; $i <= $this->seedPolicy->getUsersCount(); $i++) {
            $values[] = sprintf(
                "(%s)",
                $this->database->getDriver()->quote($this->faker->name())
            );
        }

        $valuesStr = implode(',', $values);
        $sql = "INSERT INTO users (name) VALUES $valuesStr;";

        $this->database->execute($sql);

        $logger(count($values) . ' users created.');

        unset($values, $valuesStr, $sql);
    }

    private function seedEventTypes(callable $logger): void
    {
        $logger('Seeding event types...');

        $types = $this->seedPolicy->getEventTypes();
        $values = [];

        foreach ($types as $type => $params) {
            $values[] = sprintf(
                "(%s)",
                $this->database->getDriver()->quote($type)
            );
        }

        $valuesStr = implode(',', $values);
        $sql = "INSERT INTO event_types (name) VALUES $valuesStr";

        $this->database->execute($sql);

        $logger(count($types) . ' event types created.');

        unset($types, $values, $valuesStr, $sql);
    }

    private function seedEvents(callable $logger): void
    {
        $logger('Seeding events...');

        $userIds = $this->database
            ->select('id')
            ->from('users')
            ->fetchAll(PDO::FETCH_COLUMN);

        $typeNameToId = $this->database
            ->select(['name', 'id'])
            ->from('event_types')
            ->fetchAll(PDO::FETCH_KEY_PAIR);

        if (empty($userIds) || empty($typeNameToId)) {
            $logger('No users or event types found, skipping event seeding.');

            return;
        }

        $types = $this->seedPolicy->getEventTypes();
        $typeNames = array_keys($typeNameToId);
        $referrers = $this->seedPolicy->getReferrers();

        $userIdsCount = count($userIds);
        $typeNamesCount = count($typeNames);
        $referrersCount = count($referrers);

        $startTs = strtotime('-30 days');
        $nowTs = time();

        $batchSize = $this->seedPolicy->getBatchSize();
        $totalEvents = $this->seedPolicy->getEventsCount();
        $batches = $totalEvents / $batchSize;

        for ($batch = 0; $batch < $batches; $batch++) {
            $placeholders = [];
            $values = [];
            for ($i = 0; $i < $batchSize; $i++) {
                $userId = $userIds[random_int(0, $userIdsCount - 1)];
                $typeName = $typeNames[random_int(0, $typeNamesCount - 1)];
                $referrer = $referrers[random_int(0, $referrersCount - 1)];
                $eventTypeId = $typeNameToId[$typeName];
                $page = $types[$typeName]['page'] ?? '/unknown';
                $timestamp = date('Y-m-d H:i:s', random_int($startTs, $nowTs));
                $metadata = json_encode([
                    'page' => $page,
                    'referrer' => $referrer,
                ], JSON_THROW_ON_ERROR);

                $placeholders[] = '(?, ?, ?, ?)';
                array_push(
                    $values,
                    $userId,
                    $eventTypeId,
                    $timestamp,
                    $metadata
                );
            }

            $sql = 'INSERT INTO events (user_id, type_id, timestamp, metadata) VALUES ' . implode(',', $placeholders);
            $this->database->getDriver()->query($sql, $values);
            unset($values, $placeholdersStr, $sql);

            $progress = ($batch + 1) / $batches * 100;
            $logger(sprintf('Progress: %.2f%% (%d/%d events)', $progress, ($batch + 1) * $batchSize, $totalEvents));
        }

        $logger($totalEvents . ' events created.');
    }
}
