<?php

declare(strict_types=1);

namespace App\Endpoint\Command;

use App\Domain\UserAnalytics\UseCase\Event\SeedDatabaseUseCase;
use App\Domain\UserAnalytics\ValueObject\SeedingMetricsDto;
use Closure;
use Exception;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

#[Command(name: 'events:seed')]
class EventsSeedCommand extends HyperfCommand
{
    #[Inject]
    protected SeedDatabaseUseCase $seedDatabaseUseCase;
    #[Inject]
    protected LoggerInterface $logger;
    #[Inject]
    protected CacheInterface $cache;

    protected function handle(): int
    {
        $this->warmup();
        $this->printBegin();

        try {
            $metrics = new SeedingMetricsDto();

            $this->seedDatabaseUseCase->execute(logger: $this->getLineLogger(), metrics: $metrics);

            $this->printFinish();

            $this->printMetrics($metrics);

            $this->cache->clear();

            return self::SUCCESS;
        } catch (Exception $exception) {
            $this->printError($exception);

            return self::FAILURE;
        }
    }

    private function printBegin(): void
    {
        $this->output->writeln('');
        $this->comment('════════════════════════════════');
        $this->comment('     Database Seeding Tool     ');
        $this->comment('════════════════════════════════');
        $this->output->writeln('');

        $this->info('🚀 Starting database seeding...');
        $this->output->writeln('');
    }

    private function printFinish(): void
    {
        $this->output->writeln('<fg=green>✔ Database seeding completed successfully!</fg=green>');
        $this->output->writeln('');
    }

    private function printError(Exception $exception): void
    {
        $this->output->writeln("<fg=red>❌ Error: {$exception->getMessage()}</fg=red>");
        $this->output->writeln('');

        $this->logger->error('Database seeding failed', ['exception' => $exception]);
    }

    private function printMetrics(SeedingMetricsDto $metrics): void
    {
        $this->info(sprintf('- Started at: %s', $metrics->getFormattedStartTime()));
        $this->info(sprintf('- Ended at: %s', $metrics->getFormattedEndTime()));
        $this->info(sprintf('- Duration: %.2f seconds', $metrics->getDuration()));
        $this->info(sprintf('- Memory/Worker: %.2f MB (peak: %.2f MB)', $metrics->getUsedMemoryMb(), $metrics->getPeakMemoryMb()));
        $this->info(sprintf('- CPUs used: %s', (int) shell_exec('nproc') ?: 1));
        $this->output->writeln('');
    }

    private function getLineLogger(): Closure
    {
        return function (string $message, bool $newLine = false) {
            $this->line($message);
            if ($newLine) {
                $this->output->writeln('');
            }
        };
    }

    private function warmup(): void
    {
        for ($i = 1; $i <= 100; $i++) {
            Db::connection()->getPdo()->query('SELECT 1');
        }
    }
}
