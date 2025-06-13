<?php

declare(strict_types=1);

namespace App\Application\UseCase\Event;

use App\Application\Dto\BatchParams;
use App\Application\UseCase\AbstractUseCase;
use App\Infrastructure\Generator\RandomSeedPoolGenerator;
use App\Infrastructure\Metrics\SeedingMetrics;
use Carbon\Carbon;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Coroutine\WaitGroup;
use Hyperf\DbConnection\Db;
use Hyperf\Engine\Channel;

class SeedDatabaseUseCase extends AbstractUseCase
{
    public const int MAX_COROUTINES = 8;

    private string $insertSql = 'INSERT INTO events (id,user_id, type_id, timestamp, metadata) VALUES ';
    private int $maxWorkers;

    /**
     * @var Channel<mixed>
     */
    private Channel $semaphore;
    private WaitGroup $waitGroup;

    public function __construct()
    {
        $this->maxWorkers = $this->getMaxProcs();
        $this->waitGroup = new WaitGroup();
        $this->semaphore = new Channel($this->maxWorkers);
    }

    public function execute(callable $logger, SeedingMetrics $metrics): void
    {
        $this->prepareDb($logger);
        $this->cleanDatabase($logger);

        $metrics->init();

        $this->seedUsers($logger);
        $this->seedEventTypes($logger);
        $this->seedEvents($logger);

        $metrics->collect();

        $this->checkCountEvents($logger);
        $this->setDbDefaults($logger);
    }

    private function cleanDatabase(callable $logger): void
    {
        $this->printStartMessage($logger, 'Cleaning database');

        $this->databaseManager->truncateAllTables();

        $this->printResultMessage($logger, 'Old data removed');
    }

    private function seedUsers(callable $logger): void
    {
        $this->printStartMessage($logger, 'Seeding users');

        $count = $this->eventsConfig->getUsersMaxCount();
        $values = [];
        for ($i = 1; $i <= $count; ++$i) {
            $name = $this->randomNameGenerator->generate();
            $values[] = "($i,'$name')";
        }

        $valuesStr = implode(',', $values);
        $sql = "INSERT INTO users (id, name) VALUES $valuesStr";
        Db::connection()->getPdo()->exec($sql);

        $maxId = Db::table('users')->max('id');
        Db::connection()->getPdo()->exec("SELECT setval('users_id_seq', {$maxId}, true);");

        $this->printResultMessage($logger, sprintf('%d users created', $count));
    }

    private function seedEventTypes(callable $logger): void
    {
        $this->printStartMessage($logger, 'Seeding event types');

        $types = $this->eventsConfig->getEventTypes();
        $values = [];
        foreach ($types as $typeName => $params) {
            $id = $params['id'];
            $values[] = "($id,'$typeName')";
        }

        $valuesStr = implode(',', $values);
        $sql = "INSERT INTO event_types (id, name) VALUES $valuesStr";
        Db::connection()->getPdo()->exec($sql);

        $maxId = Db::table('event_types')->max('id');
        Db::connection()->getPdo()->exec("SELECT setval('event_types_id_seq', {$maxId}, true);");

        $this->printResultMessage($logger, sprintf('%d event types created', count($types)));
    }

    private function seedEvents(callable $logger): void
    {
        $this->printStartMessage($logger, 'Preparing data for seeding events');
        $userCount = $this->eventsConfig->getUsersMaxCount();
        $userIds = range(1, $userCount);

        $eventTypes = $this->eventsConfig->getEventTypes();
        $typeNameToId = array_map(static function ($params) {
            return $params['id'];
        }, $eventTypes);

        $batchSize = $this->eventsConfig->getBatchSize();
        $totalEvents = $this->eventsConfig->getEventsCount();
        $batches = (int) ceil($totalEvents / $batchSize);
        $pool = new RandomSeedPoolGenerator($this->idGenerator, $this->eventsConfig, $userIds, $typeNameToId);
        $this->printResultMessage($logger, 'Data for seeding prepared');

        $this->printStartMessage($logger, 'Seeding events');
        $this->printResultMessage(
            $logger,
            sprintf('Total %s chunks with %s events each. Using %s workers', $batches, $batchSize, $this->maxWorkers),
            false
        );

        for ($batch = 1; $batch <= $batches; ++$batch) {
            $this->waitGroup->add();
            $batchParams = new BatchParams($batch, $batches, $batchSize);
            Coroutine::create(function () use ($batchParams, $pool) {
                $this->semaphore->push(1);
                try {
                    $this->seedChunk($batchParams, $pool);
                } finally {
                    $this->semaphore->pop();
                    $this->waitGroup->done();
                }
            });
        }

        $this->waitGroup->wait();

        $this->printResultMessage($logger, sprintf('%s chunks processed', $batches));
    }

    private function seedChunk(BatchParams $batchParams, RandomSeedPoolGenerator $pool): void
    {
        $values = $pool->getValues($batchParams->batchSize);
        $sql = $this->insertSql . implode(',', $values);
        Db::connection()->getPdo()->exec($sql);
    }

    private function checkCountEvents(callable $logger): void
    {
        $this->printStartMessage($logger, 'Checking event count in database');

        $results = $this->databaseManager->getEventCount();
        $this->printResultMessage($logger, sprintf('%s events in database', $results));
    }

    private function prepareDb(callable $logger): void
    {
        $this->printStartMessage($logger, 'Preparing database');

        $this->databaseManager->optimizeForBulkInserts();

        $this->printResultMessage($logger, 'Done');
    }

    private function setDbDefaults(callable $logger): void
    {
        $this->printStartMessage($logger, 'Setting database to defaults');

        $this->waitGroup->add();

        Coroutine::create(function () {
            $this->semaphore->push(1);
            try {
                $this->databaseManager->restoreDefaultSettings();
            } finally {
                $this->semaphore->pop();
                $this->waitGroup->done();
            }
        });

        $this->waitGroup->wait();

        $this->printResultMessage($logger, 'Done');
    }

    private function getTimestamp(): string
    {
        return Carbon::now()->format('Y-m-d H:i:s.u');
    }

    private function printStartMessage(callable $logger, string $message): void
    {
        $logger(sprintf('<fg=green>⌛ [%s] %s</fg=green>', $this->getTimestamp(), $message));
    }

    private function printResultMessage(callable $logger, string $message, bool $newLine = true): void
    {
        $logger("  <fg=cyan>▶</fg=cyan> $message", $newLine);
    }

    public function getMaxProcs(): int
    {
        $numprocs = (int) shell_exec('grep "cpu cores" /proc/cpuinfo | head -1 | awk \'{print $4}\'') ?: 8;

        return max($numprocs, self::MAX_COROUTINES);
    }
}
