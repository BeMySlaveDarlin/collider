<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\UseCase\Event;

use App\Domain\UserAnalytics\Policy\RandomSeedPool;
use App\Domain\UserAnalytics\Policy\SeedPolicy;
use App\Domain\UserAnalytics\Repository\EventTypeRepository;
use App\Domain\UserAnalytics\Repository\UserRepository;
use App\Domain\UserAnalytics\ValueObject\BatchParamsDto;
use Carbon\Carbon;
use Faker\Factory;
use Faker\Generator;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Coroutine\WaitGroup;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Engine\Channel;

class SeedDatabaseUseCase
{
    public const int MAX_COROUTINES = 10;
    private Generator $faker;
    #[Inject]
    protected UserRepository $userRepository;
    #[Inject]
    protected EventTypeRepository $eventTypeRepository;

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
        $this->checkCountEvents($logger);

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

        $batchSize = $this->seedPolicy->getBatchSize();
        $totalEvents = $this->seedPolicy->getEventsCount();
        $batches = (int) \ceil($totalEvents / $batchSize);
        $pools = new RandomSeedPool($this->seedPolicy, $userIds, $typeNameToId);

        $logger(\sprintf(' <fg=green>📦 Total %s chunks with %s events each will be processed</fg=green>', $batches, $batchSize));

        $waitGroup = new WaitGroup();
        $semaphore = new Channel($this->getMaxCoroutines());
        for ($batch = 1; $batch <= $batches; $batch++) {
            $waitGroup->add();
            $batchParams = new BatchParamsDto($batch, $batches, $batchSize);
            Coroutine::create(function () use ($waitGroup, $semaphore, $batchParams, $pools) {
                $semaphore->push(1);
                try {
                    $this->seedChunk($batchParams, $pools);
                } catch (\Throwable $e) {
                    // ignore
                } finally {
                    $semaphore->pop();
                    $waitGroup->done();
                }
            });
        }
        $waitGroup->wait();

        $this->printResultMessage($logger, sprintf('%s chunks processed', $batches));
    }

    private function checkCountEvents(callable $logger): void
    {
        $this->printStartMessage($logger, 'Checking event count in database');
        $results = Db::selectOne('SELECT COUNT(*) as count FROM events');

        $this->printResultMessage($logger, \sprintf('%s events in database', $results->count));
    }

    private function seedChunk(BatchParamsDto $batchParams, RandomSeedPool $pools): void
    {
        $pdo = Db::connection()->getPdo();
        $values = [];
        for ($i = 1; $i <= $batchParams->batchSize; $i++) {
            $values[] = sprintf(
                "(%d,%d,%s,%s)",
                $pools->getRandomUserId(),
                $pools->getRandomEventTypeId(),
                $pdo->quote($pools->getRandomTimestamp()),
                $pdo->quote($pools->getRandomMetadata()),
            );
        }
        $values = \implode(',', $values);
        $sql = "INSERT INTO events (user_id, type_id, timestamp, metadata) VALUES $values";
        $pdo->query($sql);

        unset($sql);
    }

    private function prepareDb(callable $logger): void
    {
        $this->printStartMessage($logger, 'Preparing database');

        Db::statement('SET session_replication_role = replica');

        $this->printResultMessage($logger, 'Done');
    }

    private function setDbDefaults(callable $logger): void
    {
        $this->printStartMessage($logger, 'Setting database to defaults');

        Db::statement('SET session_replication_role = DEFAULT');

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
        $numCoro = $numprocs * 2;

        return max($numCoro, self::MAX_COROUTINES);
    }
}
