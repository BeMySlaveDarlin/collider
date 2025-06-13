<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\Event;

use App\Domain\UserAnalytics\Policy\RandomSeedPool;
use App\Domain\UserAnalytics\Policy\SeedPolicy;
use App\Domain\UserAnalytics\Repository\EventRepository;
use App\Domain\UserAnalytics\Repository\EventTypeRepository;
use App\Domain\UserAnalytics\Repository\UserRepository;
use App\Domain\UserAnalytics\ValueObject\BatchParamsDto;
use Carbon\Carbon;
use Faker\Factory;
use Faker\Generator;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\WaitGroup;

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
        $this->prepareDb($logger);

        $this->cleanDatabase($logger);
        $this->seedUsers($logger);
        $this->seedEventTypes($logger);
        $this->seedEvents($logger);

        $this->setDbDefaults($logger);
    }

    private function cleanDatabase(callable $logger): void
    {
        $this->printStartMessage($logger, 'Cleaning database');

        Db::statement('TRUNCATE TABLE events RESTART IDENTITY CASCADE');
        Db::statement('TRUNCATE TABLE event_types RESTART IDENTITY CASCADE');
        Db::statement('TRUNCATE TABLE users RESTART IDENTITY CASCADE');

        $this->printResultMessage($logger, 'Old data removed');
    }

    private function seedUsers(callable $logger): void
    {
        $this->printStartMessage($logger, 'Seeding users');

        $count = $this->seedPolicy->getUsersCount();
        $names = [];
        for ($i = 0; $i < $count; $i++) {
            $names[] = $this->faker->name();
        }
        $placeholders = rtrim(str_repeat('( ? ),', $count), ',');
        $sql = "INSERT INTO users (name) VALUES $placeholders";
        Db::connection()->getPdo()->prepare($sql)->execute($names);

        $this->printResultMessage($logger, \sprintf('%d users created', $count));
    }

    private function seedEventTypes(callable $logger): void
    {
        $this->printStartMessage($logger, 'Seeding event types');

        $types = array_keys($this->seedPolicy->getEventTypes());
        $count = count($types);
        $placeholders = rtrim(str_repeat('( ? ),', $count), ',');
        $sql = "INSERT INTO event_types (name) VALUES $placeholders";
        Db::connection()->getPdo()->prepare($sql)->execute($types);

        $this->printResultMessage($logger, \sprintf('%d event types created', $count));
    }

    private function seedEvents(callable $logger): void
    {
        $this->printStartMessage($logger, 'Seeding events');

        $userIds = $this->userRepository->getAllIds();
        $typeNameToId = $this->eventTypeRepository->getNameToIdMap();
        if (empty($userIds)) {
            $this->printErrorMessage($logger, 'No users found, skipping event seeding');

            return;
        }
        if (empty($typeNameToId)) {
            $this->printErrorMessage($logger, 'No event types found, skipping event seeding');

            return;
        }

        $nowTs = time();
        $startTs = strtotime('-30 days');
        $typeNames = array_keys($typeNameToId);
        $types = $this->seedPolicy->getEventTypes();
        $referrers = $this->seedPolicy->getReferrers();
        $batchSize = $this->seedPolicy->getBatchSize();
        $totalEvents = $this->seedPolicy->getEventsCount();
        $batches = (int) \ceil($totalEvents / $batchSize);

        $logger(\sprintf(' <fg=green>📦 Total %s chunks with %s events each will be processed</fg=green>', $batches, $batchSize));

        $placeholders = rtrim(str_repeat('(?, ?, ?, ?),', $batchSize), ',');
        $sql = "INSERT INTO events (user_id, type_id, timestamp, metadata) VALUES $placeholders";
        $pools = new RandomSeedPool($userIds, $typeNameToId, $typeNames, $types, $referrers, $startTs, $nowTs);

        $waitGroup = new WaitGroup();
        $semaphore = new Channel($this->getMaxCoroutines());
        for ($batch = 1; $batch <= $batches; $batch++) {
            $waitGroup->add();
            $batchParams = new BatchParamsDto($batch, $batches, $batchSize, $sql);
            Coroutine::create(function () use ($waitGroup, $semaphore, $batchParams, $pools) {
                $semaphore->push(1);
                try {
                    $this->seedChunk($batchParams, $pools);
                } finally {
                    $semaphore->pop();
                    $waitGroup->done();
                }
            });
        }
        $waitGroup->wait();

        $logger('');
        $this->printResultMessage($logger, \sprintf('%s events created', $totalEvents));
    }

    private function seedChunk(BatchParamsDto $batchParams, RandomSeedPool $pools): void
    {
        $values = [];
        for ($i = 1; $i <= $batchParams->batchSize; $i++) {
            $values[] = $pools->getRandomUserId();
            $values[] = $pools->getRandomEventTypeId();
            $values[] = $pools->getRandomTimestamp();
            $values[] = $pools->getRandomMetadata();
        }
        $this->eventRepository->batchInsert($batchParams->sql, $values);
    }

    private function prepareDb(callable $logger): void
    {
        $this->printStartMessage($logger, 'Preparing database');

        Db::statement('SET session_replication_role = replica');
        Db::statement('SET synchronous_commit = OFF');

        $this->printResultMessage($logger, 'Done');
    }

    private function setDbDefaults(callable $logger): void
    {
        $this->printStartMessage($logger, 'Setting database to defaults');

        Db::statement('SET session_replication_role = DEFAULT');
        Db::statement('SET synchronous_commit = ON');

        $this->printResultMessage($logger, 'Done');
    }

    private function getTimestamp(): string
    {
        return Carbon::now()->format('Y-m-d H:i:s.u');
    }

    private function printStartMessage(callable $logger, string $message = ''): void
    {
        $logger(\sprintf("<fg=green>⌛ [%s] $message</fg=green>", $this->getTimestamp()));
    }

    private function printResultMessage(callable $logger, string $message = ''): void
    {
        $logger("  <fg=cyan>▶</fg=cyan> $message", true);
    }

    private function printErrorMessage(callable $logger, string $message = ''): void
    {
        $logger("  <fg=red>❌ $message</fg=red>", true);
    }

    private function getMaxCoroutines(): int
    {
        $numprocs = (int) shell_exec('nproc') ?: 2;
        $numCoro = $numprocs * 3;

        return max($numCoro, self::MAX_COROUTINES);
    }
}
