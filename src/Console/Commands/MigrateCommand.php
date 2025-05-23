<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Console\Command;
use App\Core\Database\Migrator;

class MigrateCommand extends Command
{
    protected string $name = 'migrate';
    protected string $description = 'Run database migrations';

    public function __construct(
        private readonly Migrator $migrator
    ) {}

    public function handle(array $args): int
    {
        $this->info('Running migrations...');

        try {
            $this->migrator->run();
            $this->info('Migrations completed successfully!');
            return 0;
        } catch (\Throwable $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            return 1;
        }
    }
}