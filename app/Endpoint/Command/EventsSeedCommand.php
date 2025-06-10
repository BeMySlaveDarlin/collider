<?php

declare(strict_types=1);

namespace App\Endpoint\Command;

use App\Domain\UserAnalytics\UseCase\Event\SeedDatabaseUseCase;
use Exception;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Di\Annotation\Inject;
use Psr\Log\LoggerInterface;

#[Command(name: 'events:seed')]
class EventsSeedCommand extends HyperfCommand
{
    #[Inject]
    protected SeedDatabaseUseCase $seedDatabaseUseCase;
    #[Inject]
    protected LoggerInterface $logger;

    protected function handle(): int
    {
        $this->output->writeln('');
        $this->comment('════════════════════════════════');
        $this->comment('     Database Seeding Tool     ');
        $this->comment('════════════════════════════════');
        $this->output->writeln('');

        $this->info('🚀 Starting database seeding...');
        $this->output->writeln('');
        try {
            $this->seedDatabaseUseCase->execute(
                function (string $message, bool $newLine = false) {
                    $this->line($message);
                    if ($newLine) {
                        $this->output->writeln('');
                    }
                }
            );

            $this->output->writeln('');
            $this->output->writeln('<fg=green>✔ Database seeding completed successfully!</fg=green>');
            $this->output->writeln('');

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->output->writeln('');
            $this->output->writeln("<fg=red>❌ Error: {$e->getMessage()}</fg=red>");
            $this->logger->error('Database seeding failed', ['exception' => $e]);

            return self::FAILURE;
        }
    }
}
