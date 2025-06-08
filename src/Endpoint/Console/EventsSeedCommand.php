<?php

declare(strict_types=1);

namespace App\Endpoint\Console;

use App\Domain\UserAnalytics\UseCase\Event\SeedDatabaseUseCase;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Command;

#[AsCommand(
    name: 'events:seed',
    description: 'Seed database with test data'
)]
final class EventsSeedCommand extends Command
{
    public function __construct(
        private readonly SeedDatabaseUseCase $seedDatabaseUseCase
    ) {
        parent::__construct();
    }

    protected function perform(): int
    {
        $this->info('Starting database seeding...');

        $this->seedDatabaseUseCase->execute(
            fn (string $message) => $this->info($message)
        );

        $this->info('Database seeding completed.');

        return self::SUCCESS;
    }
}
