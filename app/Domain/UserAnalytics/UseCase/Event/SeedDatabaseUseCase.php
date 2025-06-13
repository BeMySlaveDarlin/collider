<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\Event;

use App\Domain\UserAnalytics\Policy\RandomSeedPool;
use App\Domain\UserAnalytics\Policy\SeedPolicy;
use App\Domain\UserAnalytics\Repository\EventRepository;
use App\Domain\UserAnalytics\Repository\EventTypeRepository;
use App\Domain\UserAnalytics\Repository\UserRepository;
use App\Domain\UserAnalytics\ValueObject\BatchParamsDto;
use Faker\Factory;
use Faker\Generator;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\WaitGroup;

use function ceil;
use function sprintf;

class SeedDatabaseUseCase
{
    public const int MAX_COROUTINES = 14;
    private Generator $faker;
    #[Inject]
    protected UserRepository $userRepository;
    #[Inject]
    protected EventTypeRepository $eventTypeRepository;
    #[Inject]
    protected EventRepository $eventRepository;

    #[Inject]
    protected SeedPolicy $seedPolicy;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function execute(callable $logger): void
    {
        Db::statement('SET session_replication_role = replica');
        Db::statement("SET synchronous_commit = OFF");

        $this->cleanDatabase($logger);

        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

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

        Db::statement('SET session_replication_role = DEFAULT');
        Db::statement("SET synchronous_commit = ON");

        $this->userRepository->invalidateCaches();
        $this->eventTypeRepository->invalidateCaches();
        $this->eventRepository->invalidateCaches();
    }

    private function cleanDatabase(callable $logger): void
    {
        $logger('<fg=green> ● Cleaning old data...</fg=green>');

        Db::statement('TRUNCATE TABLE events RESTART IDENTITY CASCADE');
        Db::statement('TRUNCATE TABLE event_types RESTART IDENTITY CASCADE');
        Db::statement('TRUNCATE TABLE users RESTART IDENTITY CASCADE');

        $logger('  <fg=cyan>▶</fg=cyan> Old data removed', true);
    }

    private function seedUsers(callable $logger): void
    {
        $logger('<fg=green> ● Seeding users...</fg=green>');

        $count = $this->seedPolicy->getUsersCount();
        $names = [];

        for ($i = 0; $i < $count; $i++) {
            $names[] = $this->faker->name();
        }

        $placeholders = rtrim(str_repeat('( ? ),', $count), ',');

        $sql = "INSERT INTO users (name) VALUES $placeholders";

        Db::connection()->getPdo()->prepare($sql)->execute($names);

        $logger(sprintf('  <fg=cyan>▶</fg=cyan> %d users created', $count), true);
    }

    private function seedEventTypes(callable $logger): void
    {
        $logger('<fg=green> ● Seeding event types...</fg=green>');

        $types = array_keys($this->seedPolicy->getEventTypes());
        $count = count($types);

        $placeholders = rtrim(str_repeat('( ? ),', $count), ',');
        $sql = "INSERT INTO event_types (name) VALUES $placeholders";

        Db::connection()->getPdo()->prepare($sql)->execute($types);

        $logger(sprintf('  <fg=cyan>▶</fg=cyan> %d event types created', $count), true);
    }

    private function seedEvents(callable $logger): void
    {
        $logger('<fg=green> ● Seeding events...</fg=green>');

        $userIds = $this->userRepository->getAllIds();
        $typeNameToId = $this->eventTypeRepository->getNameToIdMap();
        if (empty($userIds)) {
            $logger('<fg=red>❌ Error:No users found, skipping event seeding.</fg=red>');

            return;
        }
        if (empty($typeNameToId)) {
            $logger('<fg=red>❌ Error:No event types found, skipping event seeding.</fg=red>');

            return;
        }

        $types = $this->seedPolicy->getEventTypes();
        $typeNames = array_keys($typeNameToId);
        $referrers = $this->seedPolicy->getReferrers();

        $startTs = strtotime('-30 days');
        $nowTs = time();

        $batchSize = $this->seedPolicy->getBatchSize();
        $totalEvents = $this->seedPolicy->getEventsCount();
        $batches = (int) ceil($totalEvents / $batchSize);

        $placeholders = rtrim(str_repeat('(?, ?, ?, ?),', $batchSize), ',');
        $sql = "INSERT INTO events (user_id, type_id, timestamp, metadata) VALUES $placeholders";

        $pools = new RandomSeedPool(
            userIds: $userIds,
            typeNames: $typeNames,
            typeNameToId: $typeNameToId,
            types: $types,
            referrers: $referrers,
            startTs: $startTs,
            endTs: $nowTs
        );

        $logger(sprintf(' <fg=green>● Total %s chunks with %s events each will be processed</fg=green>', $batches, $batchSize));

        $waitGroup = new WaitGroup();
        $semaphore = new Channel(self::MAX_COROUTINES);
        for ($batch = 1; $batch <= $batches; $batch++) {
            $batchParams = new BatchParamsDto($batch, $batches, $batchSize, $sql);

            $waitGroup->add();

            Coroutine::create(function () use ($waitGroup, $semaphore, $logger, $batchParams, $pools) {
                $semaphore->push(1);
                try {
                    $this->seedChunk($logger, $batchParams, $pools);
                } finally {
                    $semaphore->pop();
                    $waitGroup->done();
                }
            });
        }

        $waitGroup->wait();

        unset($pools, $userIds, $typeNameToId, $types, $typeNames, $referrers);

        $logger(sprintf('  <fg=cyan>▶</fg=cyan> %s events created', $totalEvents), true);
    }

    private function seedChunk(callable $logger, BatchParamsDto $batchParams, RandomSeedPool $pools): void
    {
        if ($batchParams->batchSize <= 0) {
            $logger(sprintf('  <fg=cyan>▶</fg=cyan> Skipping empty chunk %s/%s', $batchParams->batch, $batchParams->batches));

            return;
        }

        $placeholders = [];
        $values = [];

        for ($i = 1; $i <= $batchParams->batchSize; $i++) {
            $placeholders[] = '(?, ?, ?, ?)';
            $values[] = $pools->getRandomUserId();
            $values[] = $pools->getRandomEventTypeId();
            $values[] = $pools->getRandomTimestamp();
            $values[] = $pools->getRandomMetadata();
        }

        $this->eventRepository->batchInsert($batchParams->sql, $values);

        unset($values, $placeholders);
    }
}
